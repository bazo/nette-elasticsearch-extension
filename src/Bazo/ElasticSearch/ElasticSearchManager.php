<?php

namespace Bazo\ElasticSearch;

use Elastica\Client;

/**
 * Description of ElasticSearchManager
 *
 * @author Martin Bažík <martin@bazo.sk>
 */
class ElasticSearchManager
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
	
	public function __construct(Client $elastica, $types, $indices, $analyzers, $filters)
	{
		$this->elastica = $elastica;
		$this->types = is_array($types) ? $types : [];
		$this->indices = is_array($indices) ? $indices : [];
		$this->analyzers = $analyzers;
		$this->filters = $filters;
	}
	


	public function getTypes()
	{
		return $this->types;
	}


	public function getIndices()
	{
		return $this->indices;
	}


	public function getAnalyzers()
	{
		return $this->analyzers;
	}


	public function getFilters()
	{
		return $this->filters;
	}


}

