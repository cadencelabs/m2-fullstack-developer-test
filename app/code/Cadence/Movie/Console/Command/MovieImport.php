<?php

declare(strict_types=1);

namespace Cadence\Movie\Console\Command;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Cadence\Movie\Service\MovieImport as MovieImportService;

class MovieImport extends \Symfony\Component\Console\Command\Command
{
    /**
     * @var MovieImportService
     */
    private $movieImportService;

    public function __construct(
        MovieImportService $movieImportService,
        State $state
    ) {
        parent::__construct();

        $this->movieImportService = $movieImportService;
        $this->state = $state;
    }

    protected function configure()
    {
        $this->setName('cadence:movie:import');
        $this->setDescription('Movie import');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Start Products Import.");
        $this->state->setAreaCode(Area::AREA_ADMINHTML);
        try {
            $this->movieImportService->execute();
            $output->writeln("Finish products Import.");
            return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
        } catch (\Exception $exception) {
            $output->writeln("<error>{$exception->getMessage()}</error>");
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }
    }
}
