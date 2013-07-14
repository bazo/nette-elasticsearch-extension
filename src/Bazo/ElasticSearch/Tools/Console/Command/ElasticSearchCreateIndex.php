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
class ElasticSearchCreateIndex extends ElasticSearchCommand
{

	/** @var \Symfony\Component\Console\Output\OutputInterface */
	private $output;
	
	protected function configure()
	{
		$this
			->setName('es:index:create')
			->setDescription('creates index(es)')
		;
	}


	protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
	{
		$this->output = $output;
		
		$selectedIndexes = $this->askIndexes();
		
		foreach($selectedIndexes as $indexName) {
			$index = $this->elastica->getIndex($indexName);
			
			$indexAnalyzers  = isset($this->indices[$indexName]['analyzers']) ? $this->indices[$indexName]['analyzers'] : [];
			$indexFilters  = isset($this->indices[$indexName]['filters']) ? $this->indices[$indexName]['filters'] : [];
			
			$analyzers = [];
			foreach($indexAnalyzers as $analyzerName) {
				$analyzers[$analyzerName] = $this->analyzers[$analyzerName];
			}

			$filters = [];
			foreach($indexFilters as $filterName) {
				$filters[$filterName] = $this->filters[$filterName];
			}
			
			try {
				$index->create([
					'analysis' => [
						'analyzer' => $analyzers,
						'filter' => $filters
					]
				]);
				$output->writeln(sprintf('Index <info>%s</info> successfully created.', $indexName));
			} catch(\Elastica\Exception\ResponseException $e) {
				if(strpos($e->getMessage(), 'IndexAlreadyExistsException') !== FALSE) {
					$output->writeln(sprintf('<error>Index %s already exists. Please drop it first.</error>', $indexName));
				}
			}
		}
	}

	private function askIndexes()
	{
		$indexesToSelect = array_keys($this->indices);
		
		$selection = $this->dialog->select(
				$this->output, 
				'Please select indexes to create', 
				$indexesToSelect, 
				$default = NULL, 
				$attempts = FALSE, 
				'Value "%s" is invalid', 
				$multi = TRUE
		);

		$selectedIndexes = array_map(function($index) use ($indexesToSelect) {
			return $indexesToSelect[$index];
		}, $selection);

		return $selectedIndexes;
	}

}

