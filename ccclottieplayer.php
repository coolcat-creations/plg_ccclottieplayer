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

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\CMSPlugin;

/**
 * ccclottieplayer plugin.
 *
 * @package   ccclottieplayer
 * @since     1.0.0
 */
class PlgContentccclottieplayer extends CMSPlugin
{
	/**
	 * Application object
	 *
	 * @var    CMSApplication
	 * @since  1.0.0
	 */
	protected $app;

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

	public function onContentPrepare($context, &$article, &$params)
	{

		// Don't run this plugin when the content is being indexed
		if ($context === 'com_finder.indexer')
		{
			return;
		}

		// Simple performance check to determine whether bot should process further
		if (str_contains($article->text, 'lottieplayer') === false)
		{
			return;
		}

		// Check if LOTTIEPLAYER_REGEX is contained in $article->text

		if (preg_match_all(self::LOTTIEPLAYER_REGEX_PATTERN, $article->text, $matches, PREG_SET_ORDER))
		{
			echo HTMLHelper::_('script', 'plg_content_ccclottieplayer/lottie-player.js', array('version' => 'auto', 'relative' => true));

			$lottie = [];
			$count = 1;

			foreach ($matches as $match)
			{
				$playerparams = explode('|', $match[1]);

				$lottie[$count]['loop'] = str_contains($match[1], 'loop') !== false ? 'loop' : false;
				$lottie[$count]['autoplay'] = str_contains($match[1], 'autoplay') !== false ? 'autoplay' : false;
				$lottie[$count]['controls'] = str_contains($match[1], 'controls') !== false ? 'controls' : false;
				$lottie[$count]['hover'] = str_contains($match[1], 'hover') !== false ? 'preload' : false;
				$lottie[$count]['bounce'] = str_contains($match[1], 'bounce') !== false ? 'bounce' : false;
				$lottie[$count]['count'] = $count;

				foreach ($playerparams as $playerparam)
				{

					if (str_contains($playerparam, 'src') !== false)
					{
						$src = explode('=', $playerparam);
						$lottie[$count]['src'] = $src[1];
					}

					if (str_contains($playerparam, 'background') !== false)
					{
						$background = explode('=', $playerparam);
						$lottie[$count]['background'] = $background[1];
					}

					if (str_contains($playerparam, 'width') !== false)
					{
						$width = explode('=', $playerparam);
						$lottie[$count]['width'] = $width[1];
					}

					if (str_contains($playerparam, 'height') !== false)
					{
						$height = explode('=', $playerparam);
						$lottie[$count]['height'] = $height[1];
					}


				}


				$count++;

				$lottieplayer = [];

				foreach ($lottie as $player)
				{
					$lottieplayer = $this->getLottiePlayer($player);
				}

				$article->text = str_replace($match, $lottieplayer, $article->text);

			}


		}

	}


	/**
	 *
	 *
	 * @param   array  $player The match array
	 *
	 * @return  string $lottieplayer The replace code
	 *
	 * @since   1.0.0
	 */

	protected function getLottieplayer(array $player) : string
	{

		$lottiefile = false;

		if ($player['src'])
		{
			$file = JUri::root() . $player['src'];
			$filepath = JPATH_ROOT . '/' . $player['src'];

			if ($file && is_file($filepath) && pathinfo($filepath, PATHINFO_EXTENSION) === 'json')
			{
				$lottiefile = $file;
			}

			else
			{
				return false;
			}
		}

		$controls = $player['controls'];
		$autoplay = $player['autoplay'];
		$loop = $player['loop'];
		$hover = $player['hover'];
		$count = $player['count'];

		if ($player['bounce'] == 'bounce') {
			$bounce = 'mode="bounce"';
		} else {
			$bounce = '';
		}


		$style = "";
		$width = "";
		$height = "";

		if ($player['width'])
		{
			$width = "width:" . $player['width'] . '; ';
		}

		if ($player['height'])
		{
			$height = "height:" . $player['height'] . '; ';
		}

		if ($player['width'] || $player['height'])
		{
			$style = 'style="' . $width . ' ' . $height . '"';
		}


		$lottieplayer = '<lottie-player id="lottie-' . $count . '" 
										class="lottieplayer" 
										src="' . $lottiefile . '" 
										' . $loop . ' ' . $autoplay . ' ' . $controls . ' ' . $hover . ' ' . $bounce . ' ' . $style . ' 
										background="' . $player['background'] . '">
										
						</lottie-player>';

		return $lottieplayer;

	}

}
