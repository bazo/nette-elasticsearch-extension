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
class ElasticSearchDropType extends ElasticSearchCommand
{

	protected function configure()
	{
		$this->setName('es:info')
				->setDescription('shows info about connected cluster');
	}


	protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
	{
		$table = new Console\Helper\TableHelper;

		$output->writeln('Cluster overview:');
		
		$output->writeln('');
		
		$output->writeln('Nodes:');
		
		$cluster = $this->elastica->getCluster();
		
		$table->setHeaders(['name', 'documents', 'node', 'ip', 'port', 'hostname', 'version', 'transport address', 'http address']);
	
		$nodes = $cluster->getNodes();
		foreach($nodes as $node) {
			$name = $node->getName();
			$ip = $node->getInfo()->getIp();
			$data = $node->getInfo()->getData();
			$port = $node->getInfo()->getPort();
			$stats = $node->getStats()->get();
			$table->addRow([$data['name'], $stats['indices']['docs']['count'], $name, $ip, $port, $data['hostname'], $data['version'], $data['transport_address'], $data['http_address']]);
		}

		$table->render($output);
		$table->setRows([]);
		
		/* INFO */
		$info = $this->elastica->request('', 'GET')->getData();
		$table->setHeaders(['name', 'version', 'status', 'ok']);
		$table->addRow([$info['name'], $info['version']['number'], $info['status'], $info['ok']]);
		$table->render($output);
		$table->setRows([]);
		
		$output->writeln('');
	}


}

