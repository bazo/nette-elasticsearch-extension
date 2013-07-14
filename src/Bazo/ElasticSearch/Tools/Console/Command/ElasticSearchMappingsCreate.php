<?php

namespace Bazo\ElasticSearch\Tools\Console\Command;

use Symfony\Component\Console;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Elastica\Client;

/**
 * Prepare ElasticSearch
 * @author Martin Bažík <martin.bazik@fatchilli.com>
 */
class ElasticSearchMappingsCreate extends ElasticSearchCommand
{

	protected function configure()
	{
		$this->setName('es:configure')
				->addArgument('index', InputArgument::OPTIONAL, 'which index', 'all')
				->addArgument('type', InputArgument::OPTIONAL, 'which type', 'all')
				->addOption('dropIndex', 'di', InputOption::VALUE_NONE, 'drop index first?')
				->addOption('dropType', 'dt', InputOption::VALUE_NONE, 'drop type first?')
				->setDescription('prepares mapping for search')
				->setHelp($this->generateHelp());
	}


	private function generateHelp()
	{


		$help = 'no arguments - creates all indexes and all types mappings' . "\n";
		$help .= "\n";
		$help .= 'one argument - creates index, then creates types mapping for selected index' . "\n";
		$help .= "\n";
		$help .= 'multiple arguments encased in "" - creates multiple indexes, second multi argument specifies
                                   types for which to crete mappings,
                                   example: "web, chillout"  "articles, users"' . "\n";
		$help .= "\n";
		$help .= 'available indices:' . "\n" . implode("\n  ", array_merge(['  all'] + array_keys($this->indices)));
		$help .= "\n";
		$help .= "\n";
		$help .= 'available types:' . "\n" . implode("\n  ", array_merge(['  all'], array_keys($this->types)));

		return $help;
	}


	protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
	{
		$table = new Console\Helper\TableHelper;
		$indexArgument = $input->getArgument('index');
		$typeArgument = $input->getArgument('type');

		$dropIndexOption = $input->getOption('dropIndex');
		$dropTypeOption = $input->getOption('dropType');

		if ($indexArgument === 'all') {
			$indices = $this->indices;
		} else {
			$indices = explode(' ', $indexArgument);
		}

		$dropIndex = (bool) $dropIndexOption;
		$dropType = (bool) $dropTypeOption;

		if ($typeArgument === 'all') {
			$types = $this->types;
			$dropIndex = true;
		} else {
			$types = explode(' ', $typeArgument);
		}

		foreach ($indices as $indexName => $indexData) {

			dump($indexName, $indexData);
			exit;

			$index = $this->elastica->getIndex($indexName);

			if ($dropIndex === true) {
				try {
					$output->writeln(sprintf('dropping index %s', $indexName));
					//$response = $index->delete();
					$output->writeln(sprintf('index %s dropped', $indexName));
				} catch (\Elastica_Exception_Response $e) {
					$output->writeln(sprintf('index %s didnt exist', $indexName));
				}

				$output->writeln('');

				$output->writeln(sprintf('creating index %s', $indexName));
				$output->writeln('');
			}

			try {
				$index->create(
						[
					'analysis' => [
						'analyzer' => [
							'default_index' => [
								'type' => 'custom',
								'tokenizer' => 'lowercase',
								'filter' => ['standard', 'lowercase', 'asciifolding', 'indexNGram', 'trim', 'unique', 'stopwordsFilter']
							],
							'default_search' => [
								'type' => 'custom',
								'tokenizer' => 'lowercase',
								'filter' => ['standard', 'lowercase', 'asciifolding', 'searchNGram', 'trim', 'unique']
							]
						],
						'filter' => [
							'indexNGram' => [
								"type" => "nGram",
								"min_gram" => 4,
								"max_gram" => 50
							],
							'searchNGram' => [
								"type" => "nGram",
								"min_gram" => 4,
								"max_gram" => 50
							],
							'stopwordsFilter' => [
								'type' => 'stop',
								'stopwords' => $stopwords
							]
						]
					]
						], $dropIndex
				);
			} catch (\Elastica_Exception_Response $e) {
				$output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
			}

			//}
			$typesToCreate = array_intersect($types, $this->indicesTypes[$indexName]);

			foreach ($typesToCreate as $typeName) {

				$output->writeln(sprintf('creating mapping for type: <info>%s</info> in index: <info>%s</info>', $typeName, $indexName));
				$output->writeln('');

				if (!isset($this->mappings[$typeName])) {
					$output->writeln(sprintf('<error>Mappings for type: %s are not defined.</error>', $typeName));
					$output->writeln('');
					continue;
				}

				$elasticaType = $index->getType($typeName);

				if ($dropType === true) {
					try {
						$output->writeln(sprintf('deleting type <info>%s</info> from index: <info>%s</info>', $typeName, $indexName));
						$elasticaType->delete();
					} catch (\Elastica_Exception_Response $e) {
						$output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
					}
					$output->writeln('');
				}

				$mapping = new \Elastica_Type_Mapping();
				$mapping->setType($elasticaType);

				// Set mapping
				$mapping->setProperties(
						$this->mappings[$typeName]
				);

				// Send mapping to type
				$mapping->send();
			}
		}
	}


}

