<?php
declare(strict_types=1);
 
namespace StreamXlsx\Engine;
 
use StreamXlsx\Builder\SheetBuilder;
use StreamXlsx\Contract\ConfigRepositoryInterface;
use StreamXlsx\Dto\ExportOptions;
use StreamXlsx\Dto\SpreadsheetDefinition;
use StreamXlsx\Enum\ExportFormat;
use StreamXlsx\Enum\OutputMode;
use StreamXlsx\Factory\OutputHandlerFactory;
use StreamXlsx\Support\ConfigResolver;
 
/**
 * Memory-efficient XLSX export builder powered by xlswriter C extension.
 *
 * API is fully compatible with SpreedCore's SpreadsheetBuilder:
 *
 *   XlswriterBuilder::make()
 *       ->headers(['ID', 'Name', 'Email'])
 *       ->rows(User::cursor())
 *       ->headerColor('#0F4C81')
 *       ->freezeHeader()
 *       ->download('users.xlsx');
 *
 * Memory usage is constant (typically < 3MB) regardless of row count.
 * No PhpSpreadsheet dependency.
 */
final class XlswriterBuilder
{
    /** @var SheetBuilder */
    private $currentSheet;
 
    /** @var SheetBuilder[] */
    private $additionalSheets = [];
 
    /** @var ConfigRepositoryInterface */
    private $config;
 
    /** @var XlswriterAssembler */
    private $assembler;
 
    /** @var OutputHandlerFactory */
    private $outputHandlerFactory;
 
    private function __construct(
        ConfigRepositoryInterface $config,
        XlswriterAssembler $assembler,
        OutputHandlerFactory $outputHandlerFactory
    ) {
        $this->config               = $config;
        $this->assembler            = $assembler;
        $this->outputHandlerFactory = $outputHandlerFactory;
        $this->currentSheet         = new SheetBuilder('Sheet1');
    }
 
    public static function make(?ConfigRepositoryInterface $config = null): self
    {
        $config = $config ?? ConfigResolver::default();
 
        return new self(
            $config,
            new XlswriterAssembler(),
            new OutputHandlerFactory()
        );
    }
 
    // ── Fluent API 代理 ──
 
    /**
     * @param string $method
     * @param array $arguments
     * @return self
     */
    public function __call(string $method, array $arguments): self
    {
        $this->currentSheet->{$method}(...$arguments);
        return $this;
    }
 
    public function sheet(\Closure $callback): self
    {
        $callback($this->currentSheet);
        return $this;
    }
 
    public function addSheet(string $name, \Closure $callback): self
    {
        $sheet = new SheetBuilder($name);
        $callback($sheet);
        $this->additionalSheets[] = $sheet;
        return $this;
    }
 
    // ── 终止方法 ──
 
    /**
     * @return mixed
     */
    public function download(string $filename)
    {
        return $this->terminate(ExportOptions::forFilename($filename, OutputMode::DOWNLOAD));
    }
 
    /**
     * @return mixed
     */
    public function store(string $path)
    {
        $filename = basename($path);
        $format = ExportFormat::fromExtension($filename);
 
        return $this->terminate(new ExportOptions($format, OutputMode::STORE, $filename, $path));
    }
 
    /**
     * @return mixed
     */
    public function stream(string $filename = 'export.xlsx')
    {
        return $this->terminate(ExportOptions::forFilename($filename, OutputMode::STREAM));
    }
 
    /**
     * @return mixed
     */
    public function raw(string $filename = 'export.xlsx')
    {
        return $this->terminate(ExportOptions::forFilename($filename, OutputMode::RAW));
    }
 
    // ── 内部 ──
 
    /**
     * @return mixed
     */
    private function terminate(ExportOptions $options)
    {
        $sheets = [$this->currentSheet->build()];
        foreach ($this->additionalSheets as $sheet) {
            $sheets[] = $sheet->build();
        }
 
        $definition = new SpreadsheetDefinition($sheets);
 
        $result = $this->assembler->assemble(
            $definition,
            $this->config,
            $options->filename,
            $options->format
        );
 
        $handler = $this->outputHandlerFactory->make($options->mode);
 
        return $handler->handle($result, $options->path);
    }
}
