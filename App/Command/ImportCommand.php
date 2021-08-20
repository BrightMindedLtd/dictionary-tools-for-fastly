<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Fastly\Client;
use App\Utils;

class ImportCommand extends Command
{
	const OPTION_SERVICE_ID = 'service_id';
	const OPTION_DICTIONARY_ID = 'dictionary_id';
	const OPTION_SKIP_HEADER_ROW = 'skip-header-row';
	const ARGUMENT_FILE = 'file';

	const CSV_COL_KEY = 0;
	const CSV_COL_VALUE = 1;

	protected static $defaultName = 'dictionary:import';

	protected function configure(): void
	{
		$this
			->setDescription('Import a full dictionary from CSV, inserting, updating and deleting records as necessary')
			->setDefinition(
                new InputDefinition([
                    new InputOption(self::OPTION_SERVICE_ID, 's', InputOption::VALUE_REQUIRED, 'Fastly service ID'),
                    new InputOption(self::OPTION_DICTIONARY_ID, 'd', InputOption::VALUE_REQUIRED, 'Fastly dictionary ID'),
                    new InputOption(self::OPTION_SKIP_HEADER_ROW, null, InputOption::VALUE_NONE, 'Skip header row'),
                ])
			)
			->addArgument(self::ARGUMENT_FILE, InputArgument::REQUIRED, 'CSV input file');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$io = new SymfonyStyle($input, $output);

		$serviceId = $input->getOption(self::OPTION_SERVICE_ID);
		$dictionaryId = $input->getOption(self::OPTION_DICTIONARY_ID);
		$skipHeaderRow = (bool) $input->getOption(self::OPTION_SKIP_HEADER_ROW);
		$csvFile = $input->getArgument(self::ARGUMENT_FILE);

		if (empty($serviceId) || empty($dictionaryId)) {
			$io->error('You must specify a service and dictionary ID in order to run this command');
			return Command::INVALID;
		}

		$apiKey = Utils::promptApiKey('Fastly API Key: ', $this, $input, $output);

		try {
			$fastlyClient = new Client($apiKey);
			$currentItems = $fastlyClient->getDictionaryItems($serviceId, $dictionaryId);
			$currentItemPairs = Utils::pluck($currentItems, 'item_value', 'item_key');

			$csvHandle = fopen($csvFile, "r");
			if ($csvHandle === FALSE) {
				throw new \Exception('Could not read CSV file');
			}

			$dictionaryPatchArray = $this->createDictionaryPatchArray($csvHandle, $currentItemPairs, $skipHeaderRow);
			fclose($csvHandle);

			$fastlyClient->patchDictionaryItems($serviceId, $dictionaryId, $dictionaryPatchArray);
		} catch (\Exception $e) {
			$io->error($e->getMessage());
			return Command::FAILURE;
		}

		return Command::SUCCESS;
	}

	private function createDictionaryPatchArray($csvHandle, $currentItemPairs, $skipHeaderRow = false)
	{
		$patchArray = [];
		$newKeys = [];

		$skippedHeader = false;

		while (($data = fgetcsv($csvHandle, 0, ",")) !== FALSE) {

			if ($skipHeaderRow && !$skippedHeader) {
				$skippedHeader = true;
				continue;
			}

			if (!isset($data[self::CSV_COL_KEY]) || !isset($data[self::CSV_COL_VALUE]) || isset($data[2])) {
				throw new \Exception('Each CSV row must contain two columns: key, value');
			}

			$key = trim($data[self::CSV_COL_KEY]);
			$value = trim($data[self::CSV_COL_VALUE]);

			$newKeys[] = $key;
			if (!isset($currentItemPairs[$key]) || ($currentItemPairs[$key] !== $value)) {
				$patchArray[] = [
					"op" => "upsert",
					"item_key" => $key,
					"item_value" => $value
				];
			}
		}

		$currentItemKeys = array_keys($currentItemPairs);
		$deletedKeys = array_diff($currentItemKeys, $newKeys);
		foreach ($deletedKeys as $deleteKey) {
			$patchArray[] = [
				"op" => "delete",
				"item_key" => $deleteKey,
			];
		}

		return ['items' => $patchArray];
	}
}