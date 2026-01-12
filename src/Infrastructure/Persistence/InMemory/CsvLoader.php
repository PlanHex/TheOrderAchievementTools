<?php
namespace Infrastructure\Persistence\InMemory;

class CsvLoader
{
    private string $dataDir;

    public function __construct(string $dataDir)
    {
        $this->dataDir = rtrim($dataDir, "\\/");
    }

    /**
     * Load a CSV file and return array of associative rows.
     * The CSVs in `data/` use `|` as delimiter and `"` as enclosure.
     *
     * @param string $filename
     * @return array<int,array<string,mixed>>
     */
    public function load(string $filename): array
    {
        $path = $this->dataDir . DIRECTORY_SEPARATOR . $filename;
        if (!file_exists($path)) {
            return [];
        }

        $fh = fopen($path, 'r');
        if ($fh === false) {
            return [];
        }

        $rows = [];
        $headers = null;

        while (($line = fgets($fh)) !== false) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            // Use str_getcsv with pipe delimiter and double-quote enclosure
            $cols = str_getcsv($line, '|', '"');
            if ($headers === null) {
                // Normalize header names (remove quotes if present)
                $headers = array_map(function ($v) {
                    return trim($v, " \"\r\n");
                }, $cols);
                continue;
            }

            $clean = [];
            foreach ($cols as $i => $v) {
                $key = $headers[$i] ?? $i;
                $clean[$key] = trim($v, " \"\r\n");
            }

            $rows[] = $clean;
        }

        fclose($fh);
        return $rows;
    }
}
