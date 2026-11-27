<?php

namespace App\Support\Xlsx;

use RuntimeException;
use ZipArchive;

/**
 * سازنده فایل XLSX بدون هیچ وابستگی خارجی (فقط ZipArchive).
 *
 * یک فایل اکسل استاندارد (Office Open XML) با استایل‌های از پیش تعریف‌شده
 * تولید می‌کند: راست‌به‌چپ، فونت Tahoma، هدر رنگی، ردیف‌های یکی‌درمیان،
 * فریز سطر هدر و فیلتر خودکار.
 *
 * تمام مقادیر متنی به‌صورت inlineStr نوشته می‌شوند، بنابراین هیچ‌گاه
 * به‌عنوان فرمول تفسیر نمی‌شوند (ایمن در برابر Excel Formula Injection).
 */
class XlsxWriter
{
    /** نگاشت نام استایل منطقی به [xf عادی، xf ردیف یکی‌درمیان] */
    private const STYLE_MAP = [
        'title' => [1, 1],
        'subtitle' => [2, 2],
        'header' => [3, 3],
        'text' => [4, 5],
        'number' => [6, 7],
        'wrap' => [8, 9],
        'total' => [10, 10],
        'total_number' => [11, 11],
    ];

    /** @var list<array{cells: list<array{v: mixed, s: int}>, height: float|null}> */
    private array $rows = [];

    /** @var list<string> */
    private array $merges = [];

    /** @var array<int, float> شماره ستون (از ۱) => عرض */
    private array $columnWidths = [];

    private bool $rtl = true;

    private int $frozenRows = 0;

    private int $autoFilterHeaderRow = 0;

    private int $columnCount = 0;

    private string $creator = '';

    private string $title = '';

    public function setRightToLeft(bool $rtl): static
    {
        $this->rtl = $rtl;

        return $this;
    }

    public function setCreator(string $creator): static
    {
        $this->creator = $creator;

        return $this;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @param  list<float|int>  $widths  عرض ستون‌ها به ترتیب از ستون اول
     */
    public function setColumnWidths(array $widths): static
    {
        $this->columnWidths = [];

        foreach (array_values($widths) as $index => $width) {
            $this->columnWidths[$index + 1] = (float) $width;
        }

        return $this;
    }

    /** تعداد سطرهای بالایی که باید ثابت (فریز) بمانند. */
    public function freezeRows(int $count): static
    {
        $this->frozenRows = max(0, $count);

        return $this;
    }

    /** شماره سطر هدر برای فعال شدن فیلتر خودکار روی آن. */
    public function setAutoFilterHeaderRow(int $row): static
    {
        $this->autoFilterHeaderRow = max(0, $row);

        return $this;
    }

    /**
     * افزودن یک سطر.
     *
     * هر سلول می‌تواند مقدار ساده (string|int|float|null) یا آرایه‌ای به شکل
     * ['v' => mixed, 's' => 'style'] باشد. برای مقادیر عددیِ ساده، وقتی استایل
     * سطر «text» باشد به‌صورت خودکار از استایل «number» استفاده می‌شود تا
     * اعداد قابل محاسبه و مرتب‌سازی بمانند.
     *
     * @param  list<mixed>  $cells
     * @return int شماره سطر درج‌شده
     */
    public function addRow(array $cells, string $style = 'text', ?float $height = null, bool $zebra = false): int
    {
        $resolved = [];

        foreach (array_values($cells) as $index => $cell) {
            $value = $cell;
            $cellStyle = $style;

            if (is_array($cell)) {
                $value = $cell['v'] ?? null;
                $cellStyle = $cell['s'] ?? $style;
            } elseif ((is_int($value) || is_float($value)) && $style === 'text') {
                $cellStyle = 'number';
            }

            $resolved[] = [
                'v' => $value,
                's' => $this->resolveStyle($cellStyle, $zebra),
            ];

            $this->columnCount = max($this->columnCount, $index + 1);
        }

        $this->rows[] = ['cells' => $resolved, 'height' => $height];

        return count($this->rows);
    }

    /** ادغام سلول‌های یک سطر از ستون شروع تا پایان (برای تیتر و زیرتیتر). */
    public function mergeRow(int $row, int $fromColumn = 1, ?int $toColumn = null): static
    {
        $toColumn ??= max($this->columnCount, 1);

        if ($toColumn > $fromColumn) {
            $this->merges[] = $this->columnLetter($fromColumn).$row.':'.$this->columnLetter($toColumn).$row;
        }

        return $this;
    }

    private function resolveStyle(string $style, bool $zebra): int
    {
        $pair = self::STYLE_MAP[$style] ?? self::STYLE_MAP['text'];

        return $zebra ? $pair[1] : $pair[0];
    }

    /** ساخت فایل اکسل در مسیر داده‌شده. */
    public function save(string $path): void
    {
        if ($this->rows === []) {
            throw new RuntimeException('هیچ سطری برای ساخت فایل اکسل ثبت نشده است.');
        }

        $directory = dirname($path);
        if (! is_dir($directory) && ! @mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new RuntimeException("امکان ساخت پوشه موقت وجود ندارد: {$directory}");
        }

        $zip = new ZipArchive();
        $result = $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if ($result !== true) {
            throw new RuntimeException("امکان ساخت فایل اکسل وجود ندارد (کد خطا: {$result}).");
        }

        $zip->addFromString('[Content_Types].xml', $this->buildContentTypes());
        $zip->addFromString('_rels/.rels', $this->buildRootRels());
        $zip->addFromString('docProps/core.xml', $this->buildCoreProps());
        $zip->addFromString('xl/workbook.xml', $this->buildWorkbook());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->buildWorkbookRels());
        $zip->addFromString('xl/styles.xml', $this->buildStyles());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->buildWorksheet());

        if (! $zip->close()) {
            throw new RuntimeException('بستن فایل اکسل با خطا مواجه شد.');
        }
    }

    private function buildContentTypes(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            .'<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>'
            .'</Types>';
    }

    private function buildRootRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>'
            .'</Relationships>';
    }

    private function buildCoreProps(): string
    {
        $created = gmdate('Y-m-d\TH:i:s\Z');
        $creator = $this->escape($this->creator !== '' ? $this->creator : 'Admin Panel');
        $title = $this->escape($this->title !== '' ? $this->title : 'Report');

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" '
            .'xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" '
            .'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
            ."<dc:title>{$title}</dc:title>"
            ."<dc:creator>{$creator}</dc:creator>"
            ."<cp:lastModifiedBy>{$creator}</cp:lastModifiedBy>"
            ."<dcterms:created xsi:type=\"dcterms:W3CDTF\">{$created}</dcterms:created>"
            ."<dcterms:modified xsi:type=\"dcterms:W3CDTF\">{$created}</dcterms:modified>"
            .'</cp:coreProperties>';
    }

    private function buildWorkbook(): string
    {
        $sheetName = $this->escape($this->title !== '' ? mb_substr($this->title, 0, 31) : 'Report');

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
            .'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheets>'
            ."<sheet name=\"{$sheetName}\" sheetId=\"1\" r:id=\"rId1\"/>"
            .'</sheets>'
            .'</workbook>';
    }

    private function buildWorkbookRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            .'</Relationships>';
    }

    private function buildStyles(): string
    {
        $font = static fn (int $size, bool $bold, string $color): string => '<font>'
            .($bold ? '<b/>' : '')
            ."<sz val=\"{$size}\"/>"
            ."<color rgb=\"{$color}\"/>"
            .'<name val="Tahoma"/>'
            .'<family val="2"/>'
            .'<charset val="178"/>'
            .'</font>';

        $fonts = '<fonts count="5">'
            .$font(10, false, 'FF1F2937')   // 0: متن عادی
            .$font(14, true, 'FFFFFFFF')    // 1: تیتر
            .$font(10, true, 'FFFFFFFF')    // 2: هدر
            .$font(9, false, 'FF5B6B6A')    // 3: زیرتیتر
            .$font(10, true, 'FF0F3E3B')    // 4: جمع کل
            .'</fonts>';

        $fills = '<fills count="6">'
            .'<fill><patternFill patternType="none"/></fill>'
            .'<fill><patternFill patternType="gray125"/></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FF0E5E58"/><bgColor indexed="64"/></patternFill></fill>' // 2: تیتر
            .'<fill><patternFill patternType="solid"><fgColor rgb="FF16827D"/><bgColor indexed="64"/></patternFill></fill>' // 3: هدر
            .'<fill><patternFill patternType="solid"><fgColor rgb="FFF2FAF9"/><bgColor indexed="64"/></patternFill></fill>' // 4: یکی‌درمیان
            .'<fill><patternFill patternType="solid"><fgColor rgb="FFDCEDEB"/><bgColor indexed="64"/></patternFill></fill>' // 5: جمع کل
            .'</fills>';

        $thin = '<left style="thin"><color rgb="FFD9E2E2"/></left>'
            .'<right style="thin"><color rgb="FFD9E2E2"/></right>'
            .'<top style="thin"><color rgb="FFD9E2E2"/></top>'
            .'<bottom style="thin"><color rgb="FFD9E2E2"/></bottom>';

        $borders = '<borders count="3">'
            .'<border><left/><right/><top/><bottom/><diagonal/></border>'
            ."<border>{$thin}<diagonal/></border>"
            .'<border>'
            .'<left style="thin"><color rgb="FFD9E2E2"/></left>'
            .'<right style="thin"><color rgb="FFD9E2E2"/></right>'
            .'<top style="medium"><color rgb="FF9FC6C1"/></top>'
            .'<bottom style="thin"><color rgb="FFD9E2E2"/></bottom>'
            .'<diagonal/></border>'
            .'</borders>';

        $xf = static function (int $fontId, int $fillId, int $borderId, int $numFmtId = 0, ?string $alignment = null): string {
            $apply = ($numFmtId > 0 ? ' applyNumberFormat="1"' : '')
                .($fontId > 0 ? ' applyFont="1"' : '')
                .($fillId > 0 ? ' applyFill="1"' : '')
                .($borderId > 0 ? ' applyBorder="1"' : '')
                .($alignment !== null ? ' applyAlignment="1"' : '');

            return "<xf numFmtId=\"{$numFmtId}\" fontId=\"{$fontId}\" fillId=\"{$fillId}\" borderId=\"{$borderId}\" xfId=\"0\"{$apply}>"
                .($alignment ?? '')
                .'</xf>';
        };

        $center = '<alignment horizontal="center" vertical="center"/>';
        $right = '<alignment horizontal="right" vertical="center"/>';
        $left = '<alignment horizontal="left" vertical="center"/>';
        $wrapRight = '<alignment horizontal="right" vertical="top" wrapText="1"/>';

        $cellXfs = '<cellXfs count="12">'
            .$xf(0, 0, 0)                       // 0: پیش‌فرض
            .$xf(1, 2, 0, 0, $center)           // 1: تیتر
            .$xf(3, 0, 0, 0, $center)           // 2: زیرتیتر
            .$xf(2, 3, 1, 0, $center)           // 3: هدر
            .$xf(0, 0, 1, 0, $right)            // 4: متن
            .$xf(0, 4, 1, 0, $right)            // 5: متن یکی‌درمیان
            .$xf(0, 0, 1, 3, $left)             // 6: عدد
            .$xf(0, 4, 1, 3, $left)             // 7: عدد یکی‌درمیان
            .$xf(0, 0, 1, 0, $wrapRight)        // 8: متن چندخطی
            .$xf(0, 4, 1, 0, $wrapRight)        // 9: متن چندخطی یکی‌درمیان
            .$xf(4, 5, 2, 0, $right)            // 10: برچسب جمع کل
            .$xf(4, 5, 2, 3, $left)             // 11: عدد جمع کل
            .'</cellXfs>';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .$fonts.$fills.$borders
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            .$cellXfs
            .'<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            .'</styleSheet>';
    }

    private function buildWorksheet(): string
    {
        $lastRow = count($this->rows);
        $lastColumn = $this->columnLetter(max($this->columnCount, 1));
        $dimension = "A1:{$lastColumn}{$lastRow}";

        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            ."<dimension ref=\"{$dimension}\"/>";

        $view = '<sheetView'.($this->rtl ? ' rightToLeft="1"' : '').' workbookViewId="0">';
        if ($this->frozenRows > 0 && $lastRow > $this->frozenRows) {
            $topLeft = 'A'.($this->frozenRows + 1);
            $view .= "<pane ySplit=\"{$this->frozenRows}\" topLeftCell=\"{$topLeft}\" activePane=\"bottomLeft\" state=\"frozen\"/>"
                ."<selection pane=\"bottomLeft\" activeCell=\"{$topLeft}\" sqref=\"{$topLeft}\"/>";
        }
        $xml .= '<sheetViews>'.$view.'</sheetView></sheetViews>';

        $xml .= '<sheetFormatPr defaultRowHeight="18"/>';

        if ($this->columnWidths !== []) {
            $xml .= '<cols>';
            foreach ($this->columnWidths as $column => $width) {
                $xml .= "<col min=\"{$column}\" max=\"{$column}\" width=\"{$width}\" customWidth=\"1\"/>";
            }
            $xml .= '</cols>';
        }

        $xml .= '<sheetData>';
        foreach ($this->rows as $rowIndex => $row) {
            $rowNumber = $rowIndex + 1;
            $heightAttr = $row['height'] !== null
                ? ' ht="'.$row['height'].'" customHeight="1"'
                : '';
            $xml .= "<row r=\"{$rowNumber}\"{$heightAttr} spans=\"1:{$this->columnCount}\">";

            foreach ($row['cells'] as $cellIndex => $cell) {
                $reference = $this->columnLetter($cellIndex + 1).$rowNumber;
                $xml .= $this->buildCell($reference, $cell['v'], $cell['s']);
            }

            $xml .= '</row>';
        }
        $xml .= '</sheetData>';

        if ($this->autoFilterHeaderRow > 0 && $lastRow > $this->autoFilterHeaderRow) {
            $xml .= "<autoFilter ref=\"A{$this->autoFilterHeaderRow}:{$lastColumn}{$lastRow}\"/>";
        }

        if ($this->merges !== []) {
            $xml .= '<mergeCells count="'.count($this->merges).'">';
            foreach ($this->merges as $merge) {
                $xml .= "<mergeCell ref=\"{$merge}\"/>";
            }
            $xml .= '</mergeCells>';
        }

        $xml .= '<pageMargins left="0.3" right="0.3" top="0.5" bottom="0.5" header="0.2" footer="0.2"/>';
        $xml .= '</worksheet>';

        return $xml;
    }

    private function buildCell(string $reference, mixed $value, int $style): string
    {
        if ($value === null || $value === '') {
            return "<c r=\"{$reference}\" s=\"{$style}\"/>";
        }

        if (is_int($value) || is_float($value)) {
            return "<c r=\"{$reference}\" s=\"{$style}\"><v>{$value}</v></c>";
        }

        $text = $this->escape($this->stripInvalidXmlChars((string) $value));

        return "<c r=\"{$reference}\" s=\"{$style}\" t=\"inlineStr\">"
            ."<is><t xml:space=\"preserve\">{$text}</t></is></c>";
    }

    private function columnLetter(int $column): string
    {
        $letter = '';

        while ($column > 0) {
            $remainder = ($column - 1) % 26;
            $letter = chr(65 + $remainder).$letter;
            $column = intdiv($column - 1, 26);
        }

        return $letter;
    }

    private function stripInvalidXmlChars(string $value): string
    {
        return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value) ?? '';
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
