<?php

/**
 * Copyright (c) 2013 Martin Bažíkk <martin@bazo.sk>
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Bazo\ElasticSearch\Diagnostics;

use Nette;
use Nette\Diagnostics\Debugger;
use Psr\Log\LoggerInterface;

/**
 * @author Martin Bazik <martin@bazo.sk>
 */
class ElasticSearchPanel extends Nette\Object implements \Nette\Diagnostics\IBarPanel, LoggerInterface
{

	/**
	 * @var int
	 */
	public static $maxLength = 1000;

	/**
	 * @var int
	 */
	private $totalTime = 0;

	/**
	 * @var array
	 */
	private $queries = array();

	public function addQuery($query = null)
	{
		$this->queries[] = $query;
	}

	/**
	 * Renders HTML code for custom tab.
	 * @return string
	 */
	public function getTab()
	{
		return '<span title="ElasticSearch">'
				. '<img width="16" height="16" title="" alt="" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAIAAACQkWg2AAAABnRSTlMAAAAAAABupgeRAAAACXBIWXMAAAsTAAALEwEAmpwYAAABpElEQVR4nGNgoBxw8LCEtutH9hiwsDNjyjLBWUKyXEzMjAwMDMJy3PIGAtLa/NJafDg1sLIzR/cZuuWrMTAw2KcoQQRNgmQwNUAtVTIT1nGVEFPm0XAQg1slKMWp7SLx69vfV/e+wDWwMDAwyOkJ2CUqQh0mw4VsHr8EB7oNcvoCIa16XIJsuMKAlYP57om3f3//g2rwrdLiFWVHU/T/P8Ovb39Y2JgYGBgEJDn5RNn5xTm0nMV5hNgZuQXZMpdZIqv+9+f/1u7rhr7SnPyswrIoLnx19wvz7x9/X97+8vfPv98//jIyMp5e+0RQmlNMmefD8+8bGq/eO/n285tfn9/8/P3934cXP7Z2XmfEdDSfOId9kpKqtcjPr39Wll988+ArlmBFBj+//rl15PWH5z+0nMQFpTmv7X2JT4OwPPfv739VLETc89X+/PzHL8F568gbVg5mJmbGPz//MTAwoDvJNERW3kDw759/f3//Z2RkYGBi/Pn5t5Qm37r6Kx+ef2eARBwyOLv+CRMT4+c3P//++cfAwPD/H4OAFOfdU+8gqskBAHOljXjGGgXUAAAAAElFTkSuQmCC" />'
				. count($this->queries) . ' queries'
				. '</span>';
	}

	/**
	 * Renders HTML code for custom panel.
	 * @return string
	 */
	public function getPanel()
	{
		$s = '';
		$depth = Debugger::$maxDepth;
		Debugger::$maxDepth = 6;
		foreach ($this->queries as $query) {
			$s .= '<h2>' . $query['method'] . ': ' . $query['path'] .'</h2>';
			unset($query['method']);
			unset($query['path']);
			$s .= '<table>';
			$i = 0;
			foreach($query as $key => $variable) {
				$s .= '<tr class="'. ($i++ % 2 ? 'nette-alt' : '') .'">';
				$s .= '<th>' . htmlspecialchars($key) . '</th>';
				
				if(($key === 'data' or $key === 'query') and !empty($variable)) {
					
					//$json =json_encode($variable, JSON_PRETTY_PRINT);
					$json = Nette\Utils\Json::encode($variable, Nette\Utils\Json::PRETTY);
					
					$s .= '<td>' . Debugger::dump($variable, $return = TRUE) .'</td>';
					$s .= '<td><pre class="nette-dum"><span class="nette-dump-string">' . $json . '</span></pre></td>';
				} else {
					$s .= '<td colspan="2">' . Debugger::dump($variable, $return = TRUE) .'</td>';
				}
				
				$s .= '</tr>';
			}
			$s .= '</table>';
		}
		Debugger::$maxDepth = $depth;
		return empty($this->queries) ? '' :
			'<h1>Queries: ' . count($this->queries) . '</h1>'
			.'<div class="nette-inner elasticsearch-panel">'
			. $s
			.'</div>';
	}

	/**
	 * @param ElasticSearch\Exception $e
	 * @return type 
	 */
	public static function renderException(\Exception $e = null)
	{
		if ($e instanceof ElasticSearch\Exception) {
			$panel = NULL;
			if ($e->getQuery() !== null) {
				$panel .= '<h3>Query</h3>'
						. '<pre class="nette-dump"><span class="php-string">'
						. $e->getQuery()->getQuery()
						. '</span></pre>';
			}

			if ($panel !== NULL) {
				$panel = array(
					'tab' => 'ElasticSearch',
					'panel' => $panel
				);
			}

			return $panel;
		}
	}

	/**
	 * @return \Bazo\Extensions\ElasticSearch\Diagnostics\Panel
	 */
	public static function register()
	{
		$panel = new static();
		if(Debugger::$bar !== NULL) {
			Debugger::$blueScreen->addPanel(array($panel, 'renderException'));
			Debugger::$bar->addPanel($panel);
		}
		
		return $panel;
	}


	public function alert($message, array $context = array())
	{
		
	}


	public function critical($message, array $context = array())
	{
		
	}


	public function debug($message, array $context = array())
	{
		
	}


	public function emergency($message, array $context = array())
	{
		
	}


	public function error($message, array $context = array())
	{
		
	}


	public function info($message, array $context = array())
	{
		$this->addQuery($context);
	}


	public function log($level, $message, array $context = array())
	{
		
	}


	public function notice($message, array $context = array())
	{
		
	}


	public function warning($message, array $context = array())
	{
		
	}

}