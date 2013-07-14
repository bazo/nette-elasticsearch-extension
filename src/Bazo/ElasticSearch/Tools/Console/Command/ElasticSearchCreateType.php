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
class ElasticSearchCreateType extends ElasticSearchCommand
{

	/** @var \Symfony\Component\Console\Output\OutputInterface */
	private $output;


	protected function configure()
	{
		$this
				->setName('es:types:create')
				->setDescription('create types by given mappings')
		;
	}


	protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
	{
		$this->output = $output;

		$selectedTypes = $this->askTypes();
		$indexesForTypes = $this->getIndexesForTypes($selectedTypes);
		$selectedIndexes = $this->askIndexes($indexesForTypes);

		foreach ($selectedTypes as $typeName) {
			$typeIndexes = $indexesForTypes[$typeName];
			foreach ($selectedIndexes as $indexName) {
				if (in_array($indexName, $typeIndexes)) {
					$output->writeln(sprintf('Creating type <info>%s</info> in index <info>%s</info>', $typeName, $indexName));
					
					$properties = $this->types[$typeName]['properties'];
					$params = $this->types[$typeName]['params'];
					
					$index = $this->elastica->getIndex($indexName);
					$elasticaType = $index->getType($typeName);
					
					$elasticaMapping = new \Elastica\Type\Mapping($elasticaType, $properties);
					
					if(is_array($params)) {
						foreach($params as $param) {
							$elasticaMapping->setParam($param);
						}
					}
					
					// Send mapping to type
					try {
						$res = $elasticaMapping->send();
						var_dump($res);
						$output->writeln(sprintf('Type <info>%s</info> successfully created in index <info>%s</info>', $typeName, $indexName));
					} catch(\Elastica\Exception\ResponseException $e) {
						if(strpos($e->getMessage(), 'IndexMissingException') !== FALSE) {
							$output->writeln(sprintf('<error>Index %1$s is missing. Please create index %1$s first.</error>', $indexName));
						} elseif(strpos($e->getMessage(), 'nested: NullPointerException;') !== FALSE){
							$output->writeln(sprintf('<error>%s. Probably bad mapping.</error>', $e->getMessage()));
						}
					}
				}
			}
		}
	}


	private function askTypes()
	{
		$types = array_keys($this->types);

		$selection = $this->dialog->select(
				$this->output, 'Please select which types to create', $types, $default = NULL, $attempts = FALSE, 'Value "%s" is invalid', $multi = TRUE
		);

		$selectedTypes = array_map(function($index) use ($types) {
			return $types[$index];
		}, $selection);

		return $selectedTypes;
	}


	private function askIndexes($indexesForTypes)
	{
		$indexesToSelect = [];
		foreach ($indexesForTypes as $type => $indexes) {
			foreach ($indexes as $indexName) {
				if (!in_array($indexName, $indexesToSelect)) {
					$indexesToSelect[] = $indexName;
				}
			}
		}

		$selection = $this->dialog->select(
				$this->output, 'Please select in which index to create selected types', $indexesToSelect, $default = NULL, $attempts = FALSE, 'Value "%s" is invalid', $multi = TRUE
		);

		$selectedIndexes = array_map(function($index) use ($indexesToSelect) {
			return $indexesToSelect[$index];
		}, $selection);

		return $selectedIndexes;
	}


	private function getIndexesForTypes($selectedTypes)
	{
		$indexesForTypes = [];
		foreach ($this->indices as $indexName => $typesForIndex) {
			foreach ($selectedTypes as $selectedType) {
				if (in_array($selectedType, $typesForIndex['types'])) {
					$indexesForTypes[$selectedType][] = $indexName;
				}
			}
		}

		return $indexesForTypes;
	}


}

