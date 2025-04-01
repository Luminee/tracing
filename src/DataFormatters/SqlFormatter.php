<?php

namespace Luminee\Tracing\DataFormatters;

use Illuminate\Support\Facades\DB;

trait SqlFormatter
{
    public function toRealSql(): string
    {
        $grammar = $this->connection->getQueryGrammar();
        if ($this->type === 'query' &&
            method_exists($grammar, 'substituteBindingsIntoRawSql')) {
            try {
                $sql = $grammar->substituteBindingsIntoRawSql($this->query, $this->bindings);
                return $this->formatSql($sql);
            } catch (\Throwable $e) {
                // Continue using the old substitute
            }
        }

        if ($this->type === 'query') {
            if (empty($bindings = $this->checkBindings($this->bindings))) {
                return $this->formatSql($this->query);
            }
            $pdo = null;
            try {
                $pdo = $this->connection->getPdo();
            } catch (\Exception $e) {
                // ignore error for non-pdo laravel drivers
            }

            foreach ($bindings as $key => $binding) {
                // This regex matches placeholders only, not the question marks,
                // nested in quotes, while we iterate through the bindings
                // and substitute placeholders by suitable values.
                $regex = is_numeric($key)
                    ? "/(?<!\?)\?(?=(?:[^'\\\']*'[^'\\']*')*[^'\\\']*$)(?!\?)/"
                    : "/:{$key}(?=(?:[^'\\\']*'[^'\\\']*')*[^'\\\']*$)/";

                // Mimic bindValue and only quote non-integer and non-float data types
                if (!is_int($binding) && !is_float($binding)) {
                    if ($pdo) {
                        try {
                            $binding = $pdo->quote((string)$binding);
                        } catch (\Exception $e) {
                            $binding = $this->emulateQuote($binding);
                        }
                    } else {
                        $binding = $this->emulateQuote($binding);
                    }
                }

                $sql = preg_replace($regex, addcslashes($binding, '$'), $this->query, 1);
            }
        }

        return $this->formatSql($sql ?? $this->query);
    }

    /**
     * Check bindings for illegal (non UTF-8) strings, like Binary data.
     *
     * @param $bindings
     * @return mixed
     */
    protected function checkBindings($bindings)
    {
        foreach ($bindings as &$binding) {
            if (is_string($binding) && !mb_check_encoding($binding, 'UTF-8')) {
                $binding = '[BINARY DATA]';
            }

            if (is_array($binding)) {
                $binding = $this->checkBindings($binding);
                $binding = '[' . implode(',', $binding) . ']';
            }

            if (is_object($binding)) {
                $binding = json_encode($binding);
            }
        }

        return $bindings;
    }


    /**
     * Mimic mysql_real_escape_string
     *
     * @param string $value
     * @return string
     */
    protected function emulateQuote(string $value): string
    {
        $search = ["\\", "\x00", "\n", "\r", "'", '"', "\x1a"];
        $replace = ["\\\\", "\\0", "\\n", "\\r", "\'", '\"', "\\Z"];

        return "'" . str_replace($search, $replace, $value) . "'";
    }

    /**
     * Removes extra spaces at the beginning and end of the SQL query and its lines.
     *
     * @param string $sql
     * @return string
     */
    protected function formatSql($sql): string
    {
        $sql = preg_replace("/\?(?=(?:[^'\\\']*'[^'\\']*')*[^'\\\']*$)(?:\?)/", '?', $sql);
        return trim(preg_replace("/\s*\n\s*/", "\n", $sql));
    }
}