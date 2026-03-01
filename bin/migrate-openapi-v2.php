#!/usr/bin/env php
<?php

/**
 * Migrate OpenAPI docblock annotations (@OA\...) to PHP 8 attributes (#[OA\...]).
 * v2 — properly handles nested OA blocks by extracting Responses as separate attributes
 * and using named parameters for RequestBody, JsonContent, Property, Parameter, Schema, Items.
 *
 * Usage:
 *   php bin/migrate-openapi-v2.php <path> [--dry-run]
 */

declare(strict_types=1);

$dryRun = in_array('--dry-run', $argv, true);
$path = $argv[1] ?? null;

if (! $path) {
    echo "Usage: php bin/migrate-openapi-v2.php <path> [--dry-run]\n";
    exit(1);
}

$basePath = dirname(__DIR__);
$fullPath = str_starts_with($path, '/') ? $path : $basePath . '/' . $path;

if (is_file($fullPath)) {
    $files = [$fullPath];
} elseif (is_dir($fullPath)) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($fullPath, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    $files = [];
    foreach ($iterator as $file) {
        if ($file->getExtension() === 'php') {
            $files[] = $file->getPathname();
        }
    }
    sort($files);
} else {
    echo "Error: Path '{$fullPath}' not found.\n";
    exit(1);
}

$totalFiles = 0;
$totalConverted = 0;

foreach ($files as $file) {
    $content = file_get_contents($file);
    if (! str_contains($content, '@OA\\')) {
        continue;
    }

    $totalFiles++;
    $result = convertFile($content);

    if ($result === $content) {
        echo "SKIP (no changes): " . relativePath($file, $basePath) . "\n";
        continue;
    }

    $totalConverted++;
    $rel = relativePath($file, $basePath);

    if ($dryRun) {
        echo "WOULD CONVERT: {$rel}\n";
    } else {
        file_put_contents($file, $result);
        echo "CONVERTED: {$rel}\n";
    }
}

echo "\n" . ($dryRun ? '[DRY RUN] ' : '') . "Processed {$totalFiles} files, converted {$totalConverted}.\n";

// ---------------------------------------------------------------------------

function convertFile(string $content): string
{
    $content = ensureOAImport($content);
    $content = convertDocblockAnnotations($content);

    return $content;
}

function ensureOAImport(string $content): string
{
    if (preg_match('/use\s+OpenApi\\\\Attributes\b/', $content)) {
        return $content;
    }

    if (preg_match_all('/^use\s+[^;]+;\s*\n/m', $content, $allMatches, PREG_OFFSET_CAPTURE)) {
        $lastMatch = end($allMatches[0]);
        $lastUseEnd = $lastMatch[1] + strlen($lastMatch[0]);

        $import = "use OpenApi\\Attributes as OA;\n";
        if (! str_contains($content, $import)) {
            $content = substr($content, 0, $lastUseEnd) . $import . substr($content, $lastUseEnd);
        }
    }

    return $content;
}

function convertDocblockAnnotations(string $content): string
{
    $pattern = '/\/\*\*\s*\n(.*?)\*\//s';
    $allMatches = [];
    preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE);

    foreach ($matches[0] as $match) {
        $docblock = $match[0];
        $offset = $match[1];

        if (! str_contains($docblock, '@OA\\')) {
            continue;
        }

        $allMatches[] = [
            'docblock' => $docblock,
            'offset'   => $offset,
            'length'   => strlen($docblock),
        ];
    }

    // Process bottom-to-top to preserve offsets
    $allMatches = array_reverse($allMatches);

    foreach ($allMatches as $matchInfo) {
        $docblock = $matchInfo['docblock'];
        $offset = $matchInfo['offset'];
        $length = $matchInfo['length'];

        $oaBlocks = extractOABlocks($docblock);
        $nonOaDocblock = removeOAFromDocblock($docblock);

        $allAttributes = [];
        foreach ($oaBlocks as $oaBlock) {
            $attrs = convertOABlock($oaBlock);
            foreach ($attrs as $attr) {
                $allAttributes[] = $attr;
            }
        }

        $indent = getIndentation($content, $offset);

        $replacement = '';
        if (hasDocContent($nonOaDocblock)) {
            $replacement .= $nonOaDocblock . "\n";
        }

        foreach ($allAttributes as $attr) {
            $replacement .= indentAttribute($attr, $indent) . "\n";
        }

        $replacement = rtrim($replacement, "\n");
        $content = substr($content, 0, $offset) . $replacement . substr($content, $offset + $length);
    }

    return $content;
}

/**
 * Convert a single top-level @OA\ block into one or more #[...] attributes.
 * HTTP method blocks produce separate #[OA\Response(...)] attributes.
 *
 * @return string[]
 */
function convertOABlock(string $oaBlock): array
{
    // Determine the OA type (Get, Post, Tag, Schema, etc.)
    if (! preg_match('/^@OA\\\\(\w+)\(/', $oaBlock, $m)) {
        return [];
    }
    $type = $m[1];
    $isHttpMethod = in_array($type, ['Get', 'Post', 'Put', 'Delete', 'Patch', 'Head', 'Options'], true);

    // Extract the inner content (between the outermost parentheses)
    $openParen = strpos($oaBlock, '(');
    $innerContent = substr($oaBlock, $openParen + 1, -1); // strip outer parens

    if ($isHttpMethod) {
        return convertHttpMethodBlock($type, $innerContent);
    }

    // Non-HTTP blocks (Tag, Schema, Info, etc.) — single attribute
    $converted = convertInnerContent($innerContent, $type);
    return ['#[OA\\' . $type . '(' . $converted . ')]'];
}

/**
 * Convert an HTTP method block — the main attribute + separate Response attributes.
 *
 * @return string[]
 */
function convertHttpMethodBlock(string $method, string $innerContent): array
{
    // Split inner content into: named params, nested OA blocks
    $parts = splitInnerParts($innerContent);

    $mainParams = [];
    $responses = [];
    $requestBody = null;
    $parameters = [];

    foreach ($parts as $part) {
        $part = trim($part);
        if ($part === '') {
            continue;
        }

        if (preg_match('/^@OA\\\\Response\(/', $part)) {
            $responses[] = $part;
        } elseif (preg_match('/^@OA\\\\RequestBody\(/', $part)) {
            $requestBody = $part;
        } elseif (preg_match('/^@OA\\\\Parameter\(/', $part)) {
            $parameters[] = $part;
        } else {
            // Named parameter (key=value)
            $mainParams[] = $part;
        }
    }

    // Build main attribute
    $mainParts = [];
    foreach ($mainParams as $param) {
        $mainParts[] = convertParam($param);
    }

    // Add parameters array
    if (! empty($parameters)) {
        $paramItems = [];
        foreach ($parameters as $p) {
            $paramItems[] = convertNestedOA($p);
        }
        $mainParts[] = "parameters: [\n" . implode(",\n", array_map(fn ($p) => "    {$p}", $paramItems)) . ",\n]";
    }

    // Add requestBody
    if ($requestBody !== null) {
        $mainParts[] = 'requestBody: ' . convertNestedOA($requestBody);
    }

    $attrs = [];
    $mainContent = implode(",\n", array_map(fn ($p) => "    {$p}", $mainParts));
    $attrs[] = "#[OA\\{$method}(\n{$mainContent}\n)]";

    // Separate Response attributes
    foreach ($responses as $resp) {
        $converted = convertNestedOA($resp);
        // Parse it to extract response params and make a proper attribute
        if (preg_match('/^new OA\\\\Response\((.+)\)$/s', $converted, $rm)) {
            $attrs[] = "#[OA\\Response(\n    " . trim($rm[1]) . "\n)]";
        } else {
            $attrs[] = '#[' . preg_replace('/^new /', '', $converted) . ']';
        }
    }

    return $attrs;
}

/**
 * Split the inner content of an OA block into its top-level parts.
 * Parts are either key=value pairs or nested @OA\Something(...) blocks.
 */
function splitInnerParts(string $content): array
{
    $parts = [];
    $current = '';
    $depth = 0;
    $inString = false;
    $stringChar = '';
    $len = strlen($content);

    for ($i = 0; $i < $len; $i++) {
        $ch = $content[$i];

        // Handle string escapes
        if ($inString) {
            $current .= $ch;
            if ($ch === '\\' && $i + 1 < $len) {
                $current .= $content[++$i];
                continue;
            }
            if ($ch === $stringChar) {
                $inString = false;
            }
            continue;
        }

        if ($ch === '"' || $ch === "'") {
            $inString = true;
            $stringChar = $ch;
            $current .= $ch;
            continue;
        }

        if ($ch === '(' || $ch === '{') {
            $depth++;
            $current .= $ch;
            continue;
        }

        if ($ch === ')' || $ch === '}') {
            $depth--;
            $current .= $ch;
            continue;
        }

        if ($ch === ',' && $depth === 0) {
            $parts[] = trim($current);
            $current = '';
            continue;
        }

        $current .= $ch;
    }

    if (trim($current) !== '') {
        $parts[] = trim($current);
    }

    return $parts;
}

/**
 * Convert a key=value parameter to key: value with proper value conversion.
 */
function convertParam(string $param): string
{
    // Match key=value
    if (preg_match('/^(\w+)\s*=\s*(.+)$/s', $param, $m)) {
        $key = $m[1];
        $value = trim($m[2]);
        $converted = convertValue($value);
        return "{$key}: {$converted}";
    }

    // Already converted or other format
    return $param;
}

/**
 * Convert a value from annotation syntax to PHP attribute syntax.
 */
function convertValue(string $value): string
{
    // Double-quoted string → single-quoted
    if (preg_match('/^"(.*)"$/s', $value, $m)) {
        $inner = str_replace("'", "\\'", $m[1]);
        // Un-escape double quotes
        $inner = str_replace('\\"', '"', $inner);
        return "'{$inner}'";
    }

    // Curly braces array → square brackets
    if (str_starts_with($value, '{')) {
        return convertCurlyToSquare($value);
    }

    // Nested @OA\ block
    if (str_starts_with($value, '@OA\\')) {
        return convertNestedOA($value);
    }

    // Boolean, number, etc.
    return $value;
}

/**
 * Convert {a, b} to [a, b] and {"key": value} to ['key' => value].
 */
function convertCurlyToSquare(string $value): string
{
    $result = '';
    $len = strlen($value);
    $inString = false;
    $stringChar = '';

    for ($i = 0; $i < $len; $i++) {
        $ch = $value[$i];

        if ($inString) {
            if ($ch === '\\' && $i + 1 < $len) {
                $result .= $ch . $value[++$i];
                continue;
            }
            if ($ch === $stringChar) {
                $inString = false;
            }
            if ($ch === '"') {
                // Convert to single quote
                $result .= "'";
                $inString = false;
                continue;
            }
            $result .= $ch;
            continue;
        }

        if ($ch === '"') {
            $result .= "'";
            $inString = true;
            $stringChar = '"';
            continue;
        }

        if ($ch === '{') {
            $result .= '[';
        } elseif ($ch === '}') {
            $result .= ']';
        } elseif ($ch === ':' && ! $inString) {
            // JSON-style key:value → PHP key => value
            $result .= ' =>';
        } else {
            $result .= $ch;
        }
    }

    return $result;
}

/**
 * Convert a nested @OA\Something(...) to new OA\Something(...) with proper params.
 */
function convertNestedOA(string $oaBlock): string
{
    if (! preg_match('/^@OA\\\\(\w+)\((.+)\)$/s', $oaBlock, $m)) {
        // Fallback: just replace @OA\ with new OA\
        return str_replace('@OA\\', 'new OA\\', $oaBlock);
    }

    $type = $m[1];
    $inner = $m[2];

    // Split inner parts
    $parts = splitInnerParts($inner);
    $convertedParts = [];
    $properties = [];
    $nestedContent = null;
    $nestedSchema = null;
    $nestedItems = null;

    foreach ($parts as $part) {
        $part = trim($part);
        if ($part === '') {
            continue;
        }

        // Check if this is a nested @OA\ block
        if (preg_match('/^@OA\\\\(\w+)\(/', $part, $nm)) {
            $nestedType = $nm[1];

            if ($nestedType === 'Property') {
                $properties[] = convertNestedOA($part);
            } elseif ($nestedType === 'JsonContent' && in_array($type, ['RequestBody', 'Response', 'MediaType'], true)) {
                $nestedContent = convertNestedOA($part);
            } elseif ($nestedType === 'Schema' && in_array($type, ['Parameter', 'Property', 'Items', 'Header'], true)) {
                $nestedSchema = convertNestedOA($part);
            } elseif ($nestedType === 'Items' && in_array($type, ['Property', 'Schema', 'JsonContent'], true)) {
                $nestedItems = convertNestedOA($part);
            } elseif ($nestedType === 'MediaType' && $type === 'RequestBody') {
                $nestedContent = convertNestedOA($part);
            } else {
                // Generic nested — convert as named
                $convertedParts[] = convertParam($part);
            }
        } else {
            $convertedParts[] = convertParam($part);
        }
    }

    // Add collected nested items with named params
    if (! empty($properties)) {
        $propStr = implode(",\n        ", $properties);
        $convertedParts[] = "properties: [\n        {$propStr},\n    ]";
    }

    if ($nestedContent !== null) {
        $convertedParts[] = "content: {$nestedContent}";
    }

    if ($nestedSchema !== null) {
        $convertedParts[] = "schema: {$nestedSchema}";
    }

    if ($nestedItems !== null) {
        $convertedParts[] = "items: {$nestedItems}";
    }

    if (empty($convertedParts)) {
        return "new OA\\{$type}()";
    }

    $paramsStr = implode(", ", $convertedParts);
    return "new OA\\{$type}({$paramsStr})";
}

function convertInnerContent(string $innerContent, string $type): string
{
    $parts = splitInnerParts($innerContent);
    $convertedParts = [];
    $properties = [];

    foreach ($parts as $part) {
        $part = trim($part);
        if ($part === '') {
            continue;
        }

        if (preg_match('/^@OA\\\\Property\(/', $part)) {
            $properties[] = convertNestedOA($part);
        } elseif (preg_match('/^@OA\\\\/', $part)) {
            $convertedParts[] = convertNestedOA($part);
        } else {
            $convertedParts[] = convertParam($part);
        }
    }

    if (! empty($properties)) {
        $propStr = implode(",\n    ", $properties);
        $convertedParts[] = "properties: [\n    {$propStr},\n]";
    }

    return implode(",\n    ", $convertedParts);
}

// --- Utility functions (same as v1) ---

function extractOABlocks(string $docblock): array
{
    $lines = explode("\n", $docblock);
    $cleanContent = '';

    foreach ($lines as $line) {
        $stripped = preg_replace('/^\s*\*?\s?/', '', $line);
        if ($stripped === null) {
            continue;
        }
        if (trim($stripped) === '/**' || trim($stripped) === '/' || trim($stripped) === '*/') {
            continue;
        }
        $cleanContent .= $stripped . "\n";
    }

    $blocks = [];
    $pos = 0;
    $len = strlen($cleanContent);

    while ($pos < $len) {
        $oaStart = strpos($cleanContent, '@OA\\', $pos);
        if ($oaStart === false) {
            break;
        }

        $parenStart = strpos($cleanContent, '(', $oaStart);
        if ($parenStart === false) {
            $pos = $oaStart + 4;
            continue;
        }

        $depth = 0;
        $inString = false;
        $end = $parenStart;
        for ($i = $parenStart; $i < $len; $i++) {
            if ($inString) {
                if ($cleanContent[$i] === '\\' && $i + 1 < $len) {
                    $i++;
                    continue;
                }
                if ($cleanContent[$i] === '"') {
                    $inString = false;
                }
                continue;
            }
            if ($cleanContent[$i] === '"') {
                $inString = true;
                continue;
            }
            if ($cleanContent[$i] === '(') {
                $depth++;
            } elseif ($cleanContent[$i] === ')') {
                $depth--;
                if ($depth === 0) {
                    $end = $i;
                    break;
                }
            }
        }

        $block = substr($cleanContent, $oaStart, $end - $oaStart + 1);
        $blocks[] = trim($block);
        $pos = $end + 1;
    }

    return $blocks;
}

function removeOAFromDocblock(string $docblock): string
{
    $lines = explode("\n", $docblock);
    $resultLines = [];
    $inOA = false;
    $parenDepth = 0;

    foreach ($lines as $line) {
        $stripped = trim(preg_replace('/^\s*\*?\s?/', '', $line) ?? '');

        if (! $inOA && preg_match('/@OA\\\\/', $stripped)) {
            $inOA = true;
            $parenDepth = 0;
            $parenDepth += substr_count($stripped, '(') - substr_count($stripped, ')');
            if ($parenDepth <= 0) {
                $inOA = false;
            }
            continue;
        }

        if ($inOA) {
            $parenDepth += substr_count($stripped, '(') - substr_count($stripped, ')');
            if ($parenDepth <= 0) {
                $inOA = false;
            }
            continue;
        }

        $resultLines[] = $line;
    }

    return implode("\n", $resultLines);
}

function hasDocContent(string $docblock): bool
{
    $cleaned = preg_replace('/\/\*\*|\*\/|\*/', '', $docblock);
    $cleaned = trim($cleaned ?? '');

    return $cleaned !== '' && $cleaned !== '/';
}

function getIndentation(string $content, int $offset): string
{
    $lineStart = strrpos($content, "\n", $offset - strlen($content));
    if ($lineStart === false) {
        $lineStart = 0;
    } else {
        $lineStart++;
    }

    $line = substr($content, $lineStart, $offset - $lineStart);
    preg_match('/^(\s*)/', $line, $m);

    return $m[1] ?? '';
}

function indentAttribute(string $attribute, string $indent): string
{
    $lines = explode("\n", $attribute);
    $result = [];
    foreach ($lines as $i => $line) {
        if ($i === 0) {
            $result[] = $indent . $line;
        } else {
            $result[] = $indent . '    ' . trim($line);
        }
    }

    return implode("\n", $result);
}

function relativePath(string $path, string $base): string
{
    return str_replace($base . '/', '', $path);
}
