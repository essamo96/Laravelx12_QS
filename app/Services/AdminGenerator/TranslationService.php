<?php

namespace App\Services\AdminGenerator;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class TranslationService
{
    public function update(string $tableName, array $translations): void
    {
        $langDirAr = base_path('lang/ar');
        $langDirEn = base_path('lang/en');
        File::ensureDirectoryExists($langDirAr);
        File::ensureDirectoryExists($langDirEn);

        $arFile = $langDirAr.'/app.php';
        $enFile = $langDirEn.'/app.php';
        $arData = File::exists($arFile) ? include $arFile : [];
        $enData = File::exists($enFile) ? include $enFile : [];

        foreach ($translations as $key => $value) {
            if (! is_array($value)) {
                continue;
            }

            $arLabel = $value['ar'] ?? $value['en'] ?? $key;
            $enLabel = $value['en'] ?? $key;

            $enLabel = $this->ensureEnglishOnly($key, (string) $enLabel);
            $arLabel = (string) $arLabel;

            $arData[$key] = $arLabel;
            $enData[$key] = $enLabel;
        }

        $this->writePhpArray($arFile, $arData);
        $this->writePhpArray($enFile, $enData);
    }

    /**
     * يمنع كتابة نص عربي أو بقايا @lang(...) في ملف lang/en.
     */
    private function ensureEnglishOnly(string $columnKey, string $label): string
    {
        $label = trim($label);
        $fallback = Str::headline(str_replace('_', ' ', $columnKey));

        if ($label === '' || preg_match('/@lang\s*\(/i', $label)) {
            return $fallback;
        }

        if (preg_match('/\p{Arabic}/u', $label)) {
            return $fallback;
        }

        return $label;
    }

    private function writePhpArray(string $path, array $data): void
    {
        $export = var_export($data, true);
        $content = "<?php\n\nreturn {$export};\n";
        File::put($path, $content);
    }
}
