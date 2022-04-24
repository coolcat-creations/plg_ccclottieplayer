<?php
/**
 * @package    ccclottieplayer
 *
 * @author     elisa <your@email.com>
 * @copyright  A copyright
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       http://your.url.com
 */

defined('_JEXEC') or die;
error_reporting(E_ALL);

// error reporting level maximum

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\CMSPlugin;

/**
 * ccclottieplayer plugin.
 *
 * @package   ccclottieplayer
 * @since     1.0.0
 */
class plgContentccclottieplayer extends CMSPlugin
{
	/**
	 * Application object
	 *
	 * @var    CMSApplication
	 * @since  1.0.0
	 */
	protected $app;


	/**
	 * The regular expression to search for lottieplayer
	 */

	const LOTTIEPLAYER_REGEX_PATTERN = '#{lottieplayer (.*?)}#s';

	/**
	 * Database object
	 *
	 * @var    JDatabaseDriver
	 * @since  1.0.0
	 */
	protected $db;

	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  1.0.0
	 */
	protected $autoloadLanguage = true;


	/**
	 * oncontentprepare event handler.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */

	public function onContentPrepare($context, &$article, &$params, $page = 0)
	{

		// Don't run this plugin when the content is being indexed
		if ($context == 'com_finder.indexer'
			// Don't run this plugin when we edit an custom module in frontend
			|| $this->app->input->getCmd('option') == 'com_config'
			// Don't run this plugin when we edit the content in frontend
			|| $this->app->input->getCmd('layout') == 'edit') {
			return;
		}

		// Check if we are in the right component
		if ($this->app->input->get('option') != 'com_content') {
			return;
		}

		// Check if LOTTIEPLAYER_REGEX is contained in $article->text

		if (preg_match_all(self::LOTTIEPLAYER_REGEX_PATTERN, $article->text, $matches, PREG_SET_ORDER)) {

			echo HTMLHelper::_('script', 'plg_content_ccclottieplayer/lottie-player.js', array('version' => 'auto', 'relative' => true));


			$lottie = [];
			$count = 1;

			foreach ($matches as $match) {

				$playerparams = explode('|', $match[1]);

				echo '<pre>';
				print_r($playerparams);
				echo '</pre>';

				foreach ($playerparams as $playerparam) {

					if (strpos($playerparam, 'path') !== false) {
						$path = explode('=', $playerparam);
						$lottie[$count]['path'] = $path[1];
					}

					if (strpos($playerparam, 'background') !== false) {
						$background = explode('=', $playerparam);
						$lottie[$count]['background'] = $background[1];
					}

					if (strpos($playerparam, 'width') !== false) {
						$width = explode('=', $playerparam);
						$lottie[$count]['width'] = $width[1];
					}

					if (strpos($playerparam, 'height') !== false) {
						$height = explode('=', $playerparam);
						$lottie[$count]['height'] = $height[1];
					}

					if (strpos($playerparam, 'loop') !== false) {
						$lottie[$count]['loop'] = 'loop';
					} else {
						$lottie[$count]['loop'] = false;
					}


					if (strpos($playerparam, 'autoplay') !== false) {
						$lottie[$count]['autoplay'] = 'autoplay';
					} else {
						$lottie[$count]['autoplay'] = false;
					}

					if (strpos($playerparam, 'controls') !== false) {
						$lottie[$count]['controls'] = 'controls';
					} else {
						$lottie[$count]['controls'] = false;
					}

					$lottie[$count]['count'] = $count;


				}


				$count++;

				$lottieplayer = [];

				foreach ($lottie as $player) {
					$lottieplayer = $this->getLottiePlayer($player);
				}

				$article->text = str_replace($match, $lottieplayer, $article->text);

			}


		}

	}


	/**
	 *
	 *
	 * @param array $match The match array
	 *
	 * @return  string
	 *
	 * @since   1.0.0
	 */

	protected function getLottieplayer($player)
	{


		if ($player['path']) {
			// check if the file exists in on this domain

			$file = JUri::root() . $player['path'];
			$filepath = JPATH_ROOT . '/' . $player['path'];

			// if file exists in Joomla
			if (JFile::exists($filepath)) {
				$lottiefile = $file;
			} else {
				return;
			}
		}

		if ($player['controls'] == 'controls') {
			$controls = 'controls';
		} else {
			$controls = '';
		}
		if ($player['loop'] == 'loop') {
			$loop = 'loop';
		} else {
			$loop = '';
		}
		if ($player['autoplay'] == 'autoplay') {
			$autoplay = 'autoplay';
		} else {
			$autoplay = '';
		}

		if ($player['count']) {
			$count = $player['count'];
		}

		$style = "";

		if ($player['width']) {
			$width = "width:" . $player['width'] . '; ';
		}

		if ($player['height']) {
			$height = "height:" . $player['height'] . '; ';
		}

		if ($player['width'] || $player['height']) {
			$style = 'style="' . $width . ' ' . $height . '"';
		}


		$lottieplayer = '<lottie-player id="lottie-' . $player['count'] . '" class="lottieplayer" src="' . $lottiefile . '" ' . $loop . $autoplay . $controls . $style . '" background="' . $player['background'] . '"></lottie-player>';

		return $lottieplayer;

	}

}
