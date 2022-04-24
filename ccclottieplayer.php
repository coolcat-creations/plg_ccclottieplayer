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
		if ($context === 'com_finder.indexer')
		{
			return;
		}

		// Simple performance check to determine whether bot should process further
		if (strpos($article->text, 'lottieplayer') === false)
		{
			return;
		}

		// Check if LOTTIEPLAYER_REGEX is contained in $article->text

		if (preg_match_all(self::LOTTIEPLAYER_REGEX_PATTERN, $article->text, $matches, PREG_SET_ORDER)) {

			echo HTMLHelper::_('script', 'plg_content_ccclottieplayer/lottie-player.js', array('version' => 'auto', 'relative' => true));

			$lottie = [];
			$count = 1;

			foreach ($matches as $match) {

				$playerparams = explode('|', $match[1]);

				if (strpos($match[1], 'loop') !== false) {
					$lottie[$count]['loop'] = 'loop';
				} else {
					$lottie[$count]['loop'] = false;
				}

				if (strpos($match[1], 'autoplay') !== false) {
					$lottie[$count]['autoplay'] = 'autoplay';
				} else {
					$lottie[$count]['autoplay'] = false;
				}

				if (strpos($match[1], 'controls') !== false) {
					$lottie[$count]['controls'] = 'controls';
				} else {
					$lottie[$count]['controls'] = false;
				}

				if (strpos($match[1], 'hover') !== false) {
					$lottie[$count]['hover'] = 'hover';
				} else {
					$lottie[$count]['hover'] = false;
				}

				if (strpos($match[1], 'bounce') !== false) {
					$lottie[$count]['bounce'] = 'bounce';
				} else {
					$lottie[$count]['bounce'] = false;
				}


				$lottie[$count]['count'] = $count;

				foreach ($playerparams as $playerparam) {

					if (strpos($playerparam, 'src') !== false) {
						$src = explode('=', $playerparam);
						$lottie[$count]['src'] = $src[1];
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


		if ($player['src']) {
			// check if the file exists in on this domain

			$file = JUri::root() . $player['src'];
			$filepath = JPATH_ROOT . '/' . $player['src'];

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

		if ($player['bounce'] == 'bounce') {
			$bounce = 'mode="bounce"';
		} else {
			$bounce = '';
		}

		if ($player['loop'] == 'loop') {
			$loop = 'loop';
		} else {
			$loop = '';
		}

		if ($player['hover'] == 'hover') {
			$hover = 'hover';
		} else {
			$hover = '';
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


		$lottieplayer = '<lottie-player id="lottie-' . $count . '" class="lottieplayer" src="' . $lottiefile . '" ' . $loop . ' ' . $autoplay . ' ' .$controls . ' ' . $hover . ' ' . $bounce . ' ' .$style . ' background="' . $player['background'] . '"></lottie-player>';

		return $lottieplayer;

	}

}
