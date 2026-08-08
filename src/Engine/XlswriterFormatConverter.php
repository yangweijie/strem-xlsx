<?php
declare(strict_types=1);
 
namespace StreamXlsx\Engine;
 
use StreamXlsx\Dto\RowStyleOptions;
use StreamXlsx\Dto\StyleOptions;
use StreamXlsx\Helper\ColorHelper;
use Vtiful\Kernel\Format;
 
final class XlswriterFormatConverter
{
    /**
     * @param mixed $fileHandle
     * @return mixed
     */
    public static function toFormat(StyleOptions $style, $fileHandle)
    {
        $format = new Format($fileHandle);
 
        if ($style->bold) {
            $format->bold();
        }
        if ($style->italic) {
            $format->italic();
        }
        if ($style->fontSize !== null) {
            $format->fontSize($style->fontSize);
        }
        if ($style->fontColor !== null) {
            $format->fontColor(ColorHelper::toInt($style->fontColor));
        }
        if ($style->backgroundColor !== null) {
            $format->background(ColorHelper::toInt($style->backgroundColor));
        }
        if ($style->wrapText) {
            $format->wrap();
        }
        if ($style->fontFamily !== null) {
            $format->fontFamily($style->fontFamily);
        }
 
        $hAlign = self::mapHAlign($style->alignment);
        $vAlign = $style->verticalCenter
            ? Format::FORMAT_ALIGN_VERTICAL_CENTER
            : Format::FORMAT_ALIGN_VERTICAL_BOTTOM;
 
        $format->align($hAlign, $vAlign);
 
        return $format->toResource();
    }

    /**
     * @param mixed $fileHandle
     * @param bool $isOdd
     * @return mixed|null
     */
    public static function toAlternateFormat(RowStyleOptions $rowStyle, $fileHandle, bool $isOdd)
    {
        if ($rowStyle->alternateColor === null || $isOdd) {
            return null;
        }
        return (new Format($fileHandle))
            ->background(ColorHelper::toInt($rowStyle->alternateColor))
            ->toResource();
    }

    /**
     * @param mixed $fileHandle
     * @return mixed
     */
    public static function toBorderFormat($fileHandle)
    {
        return (new Format($fileHandle))
            ->border(Format::BORDER_THIN)
            ->toResource();
    }
 
    private static function mapHAlign(?string $alignment): int
    {
        if ($alignment === 'left') {
            return Format::FORMAT_ALIGN_LEFT;
        }
        if ($alignment === 'center') {
            return Format::FORMAT_ALIGN_CENTER;
        }
        if ($alignment === 'right') {
            return Format::FORMAT_ALIGN_RIGHT;
        }
        return Format::FORMAT_ALIGN_LEFT;
    }
}
