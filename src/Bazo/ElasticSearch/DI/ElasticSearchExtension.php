<?php

namespace Bazo\ElasticSearch\DI;

/**
 * Description of ElasticSearchExtension
 *
 * @author Martin Bažík <martin@bazo.sk>
 */
class ElasticSearchExtension extends \Nette\DI\CompilerExtension
{

	private $defaults = [
		'config' => [
			'host' => NULL,
			'port' => NULL,
			'path' => NULL,
			'url' => NULL,
			'transport' => NULL,
			'persistent' => TRUE,
			'timeout' => NULL,
			'connections' => [], // host, port, path, timeout, transport, persistent, timeout, config -> (curl, headers, url)
			'roundRobin' => FALSE,
			'log' => '%debugMode%',
			'retryOnConflict' => 0]
		,
		'mapping' => [
			'types' => [],
			'indices' => []
		],
		'analyzers' => [],
		'filters' => []
	];


	public function loadConfiguration()
	{
		$containerBuilder = $this->getContainerBuilder();

		$config = $this->getConfig($this->defaults);

		$debugMode = $containerBuilder->expand('%debugMode%');

		$commandArguments = ['@'.$this->prefix('elastica'), $config['types'], $config['indices'], $config['analyzers'], $config['filters']];
		
		$containerBuilder
				->addDefinition($this->prefix('panel'))
				->setClass('Bazo\ElasticSearch\Diagnostics\ElasticSearchPanel')
				->setFactory('Bazo\ElasticSearch\Diagnostics\ElasticSearchPanel::register');
		
		$elasticaDefinition = $containerBuilder->addDefinition($this->prefix('elastica'))
				->setClass('Elastica\Client', [$config['config']]);
		if($debugMode) {
			$elasticaDefinition->addSetup('setLogger', '@'. $this->prefix('panel'));
		}
		
		$containerBuilder->addDefinition('elastica')
				->setClass('Elastica\Client')
				->setFactory('@container::getService', array($this->prefix('elastica')))
				->setAutowired(FALSE);
		
		$containerBuilder
				->addDefinition($this->prefix('elasticSearchManager'))
				->setClass('Bazo\ElasticSearch\ElasticSearchManager', $commandArguments)
		;
		
		$containerBuilder
				->addDefinition($this->prefix('infoCommand'))
				->setClass('Bazo\ElasticSearch\Tools\Console\Command\ElasticSearchInfo', ['@'.$this->prefix('elastica')])
				->addTag('consoleCommand')
				->setAutowired(FALSE)
		;
		
		$containerBuilder
				->addDefinition($this->prefix('createIndexCommand'))
				->setClass('Bazo\ElasticSearch\Tools\Console\Command\ElasticSearchCreateIndex', $commandArguments)
				->addTag('consoleCommand')
				->setAutowired(FALSE)
		;
		
		$containerBuilder
				->addDefinition($this->prefix('dropIndexCommand'))
				->setClass('Bazo\ElasticSearch\Tools\Console\Command\ElasticSearchDropIndex', $commandArguments)
				->addTag('consoleCommand')
				->setAutowired(FALSE)
		;
		
		$containerBuilder
				->addDefinition($this->prefix('createTypeCommand'))
				->setClass('Bazo\ElasticSearch\Tools\Console\Command\ElasticSearchCreateType', $commandArguments)
				->addTag('consoleCommand')
				->setAutowired(FALSE)
		;
		
		$containerBuilder
				->addDefinition($this->prefix('prepareCommand'))
				->setClass('Bazo\ElasticSearch\Tools\Console\Command\ElasticSearchMappingsCreate', $commandArguments)
				->addTag('consoleCommand')
				->setAutowired(FALSE)
		;
	}


	public function beforeCompile()
	{
		$containerBuilder = $this->getContainerBuilder();
	}


}

