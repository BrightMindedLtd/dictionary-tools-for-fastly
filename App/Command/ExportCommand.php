<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Output\OutputInterface;
use App\Fastly\Client;
use App\Utils;

class ExportCommand extends Command
{
	const OPTION_SERVICE_ID = 'service_id';
	const OPTION_DICTIONARY_ID = 'dictionary_id';
	const OPTION_SKIP_HEADER_ROW = 'skip-header-row';

	protected static $defaultName = 'dictionary:export';

	protected function configure(): void
	{
		$this
			->setDescription('Export a full dictionary to CSV')
			->setDefinition(
                new InputDefinition([
                    new InputOption(self::OPTION_SERVICE_ID, 's', InputOption::VALUE_REQUIRED, 'Fastly service ID'),
                    new InputOption(self::OPTION_DICTIONARY_ID, 'd', InputOption::VALUE_REQUIRED, 'Fastly dictionary ID'),
                    new InputOption(self::OPTION_SKIP_HEADER_ROW, null, InputOption::VALUE_NONE, 'Skip header row'),
                ])
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$io = new SymfonyStyle($input, $output);

		$serviceId = $input->getOption(self::OPTION_SERVICE_ID);
		$dictionaryId = $input->getOption(self::OPTION_DICTIONARY_ID);
		$skipHeaderRow = (bool) $input->getOption(self::OPTION_SKIP_HEADER_ROW);

		if (empty($serviceId) || empty($dictionaryId)) {
			$io->error('You must specify a service and dictionary ID in order to run this command');
			return Command::INVALID;
		}

		$apiKey = Utils::promptApiKey('Fastly API Key: ', $this, $input, $output);

		$fastlyClient = new Client($apiKey);
		$currentItems = $fastlyClient->getDictionaryItems($serviceId, $dictionaryId);
		$currentItemPairs = Utils::pluck($currentItems, 'item_value', 'item_key');

		if (!$skipHeaderRow) {
			fputcsv(STDOUT, [
				'Dictionary Key', 'Dictionary Value'
			]);
		}

		foreach ($currentItemPairs as $key => $value) {
			fputcsv(STDOUT, [
				$key, $value
			]);
		}

		return Command::SUCCESS;
	}
}