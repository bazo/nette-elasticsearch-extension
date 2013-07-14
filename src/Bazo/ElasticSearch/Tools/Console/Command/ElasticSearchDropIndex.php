<?php

namespace Bazo\ElasticSearch\Tools\Console\Command;

use Symfony\Component\Console;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Prepare ElasticSearch
 * @author Martin Bažík <martin.bazik@fatchilli.com>
 */
class ElasticSearchDropIndex extends ElasticSearchCommand
{

	/** @var \Symfony\Component\Console\Output\OutputInterface */
	private $output;
	
	protected function configure()
	{
		$this
			->setName('es:index:drop')
			->setDescription('drops index(es)')
		;
	}


	protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
	{
		$this->output = $output;
		
		$selectedIndexes = $this->askIndexes();
		
		foreach($selectedIndexes as $indexName) {
			$index = $this->elastica->getIndex($indexName);
			
			try {
				$index->delete();
				$output->writeln(sprintf('Index <info>%s</info> successfully dropped.', $indexName));
			} catch(\Elastica\Exception\ResponseException $e) {
				if(strpos($e->getMessage(), 'IndexMissingException') !== FALSE) {
					$output->writeln(sprintf('<error>Index %s doesn\'t exist. Please create it first.</error>', $indexName));
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

