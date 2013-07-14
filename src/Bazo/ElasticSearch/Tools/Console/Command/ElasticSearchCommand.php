<?php

namespace Bazo\ElasticSearch\Tools\Console\Command;

use Symfony\Component\Console;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Elastica\Client;

/**
 * Description of ElasticSearchCommand
 *
 * @author Martin Bažík <martin@bazo.sk>
 */
abstract class ElasticSearchCommand extends Console\Command\Command
{
	/** @var \Elastica\Client */
	protected $elastica;
	
	/** @var array */
	protected $types;

	/** @var array */
	protected $indices;

	/** @var array */
	protected $analyzers;

	/** @var array */
	protected $filters;

	/** @var \Symfony\Component\Console\Helper\DialogHelper */
	protected $dialog;

	public function __construct(Client $elastica, $types, $indices, $analyzers, $filters)
	{
		$this->elastica = $elastica;
		$this->types = is_array($types) ? $types : [];
		$this->indices = is_array($indices) ? $indices : [];
		$this->analyzers = $analyzers;
		$this->filters = $filters;
		$this->dialog = $dialog = new Console\Helper\DialogHelper;
		parent::__construct(NULL);
	}
}

