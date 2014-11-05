<?php

namespace Bazo\ElasticSearch\DI;

/**
 * @author Martin Bažík <martin@bazo.sk>
 */
class ElasticSearchExtension extends \Nette\DI\CompilerExtension
{

	private $defaults = [
		'mapping'	 => [
			'types'		 => [],
			'indices'	 => []
		],
		'analyzers'	 => [],
		'filters'	 => []
	];


	public function loadConfiguration()
	{
		$containerBuilder = $this->getContainerBuilder();

		$config = $this->getConfig($this->defaults);

		$commandArguments = [$config['types'], $config['indices'], $config['analyzers'], $config['filters']];

		$containerBuilder
				->addDefinition($this->prefix('elasticSearchManager'))
				->setClass('Bazo\ElasticSearch\ElasticSearchManager', $commandArguments)
		;

		$containerBuilder
				->addDefinition($this->prefix('infoCommand'))
				->setClass('Bazo\ElasticSearch\Tools\Console\Command\ElasticSearchInfo')
				->addTag('console.command')
				->addTag('kdyby.console.command')
				->setAutowired(FALSE)
		;

		$containerBuilder
				->addDefinition($this->prefix('createIndexCommand'))
				->setClass('Bazo\ElasticSearch\Tools\Console\Command\ElasticSearchCreateIndex', $commandArguments)
				->addTag('console.command')
				->addTag('kdyby.console.command')
				->setAutowired(FALSE)
		;

		$containerBuilder
				->addDefinition($this->prefix('dropIndexCommand'))
				->setClass('Bazo\ElasticSearch\Tools\Console\Command\ElasticSearchDropIndex', $commandArguments)
				->addTag('console.command')
				->addTag('kdyby.console.command')
				->setAutowired(FALSE)
		;

		$containerBuilder
				->addDefinition($this->prefix('createTypeCommand'))
				->setClass('Bazo\ElasticSearch\Tools\Console\Command\ElasticSearchCreateType', $commandArguments)
				->addTag('console.command')
				->addTag('kdyby.console.command')
				->setAutowired(FALSE)
		;

		$containerBuilder
				->addDefinition($this->prefix('prepareCommand'))
				->setClass('Bazo\ElasticSearch\Tools\Console\Command\ElasticSearchMappingsCreate', $commandArguments)
				->addTag('console.command')
				->addTag('kdyby.console.command')
				->setAutowired(FALSE)
		;
	}


}
