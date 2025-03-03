<?php
/**
 * Kunena Plugin
 *
 * @package         Kunena.Plugins
 * @subpackage      Easysocial
 *
 * @copyright      Copyright (C) 2008 - 2021 Kunena Team. All rights reserved.
 * @copyright      Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
 * @license        GNU/GPL, see LICENSE.php
 * EasySocial is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */
defined('_JEXEC') or die('Unauthorized Access');

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;

/**
 * @package     Kunena
 *
 * @since       Kunena 5.0
 */
class KunenaProfileEasySocial extends KunenaProfile
{
	protected $params = null;

	/**
	 * KunenaProfileEasySocial constructor.
	 *
	 * @param $params
	 * @since       Kunena 5.0
	 */
	public function __construct($params)
	{
		$this->params = $params;
	}

	/**
	 * @param   string $action action
	 * @param   bool $xhtml xhtml
	 *
	 * @return boolean
	 * @throws Exception
	 * @since       Kunena 5.0
	 */
	public function getUserListURL($action = '', $xhtml = true)
	{
		$config = KunenaFactory::getConfig();
		$my     = Factory::getUser();

		if ($config->userlist_allowed == 0 && $my->guest)
		{
			return false;
		}

		return FRoute::users(array(), $xhtml);
	}

	/**
	 * @param        $userid
	 * @param   string $task task
	 * @param   bool $xhtml xhtml
	 *
	 * @return string
	 * @since       Kunena 5.0
	 */
	public function getProfileURL($userid, $task = '', $xhtml = true)
	{
		if ($userid)
		{
			$user = ES::user($userid);

			// When simple urls are enabled, we just hardcode the url
			$config  = ES::config();
			$jConfig = ES::jConfig();

			if (!ES::isSh404Installed() && $config->get('users.simpleUrl') && $jConfig->getValue('sef'))
			{
				$url = rtrim(Uri::root(), '/') . '/' . $user->getAlias(false);

				return $url;
			}

			// If it's not configured for simple urls, just set the alias
			$alias = $user->getAlias();
		}
		else
		{
			$alias = $userid;
		}

		$options = array('id' => $alias);

		if ($task)
		{
			$options['layout'] = $task;
		}

		$url = FRoute::profile($options, $xhtml);

		return $url;
	}

	/**
	 * @param   int $limit limit
	 *
	 * @return array|void
	 * @since       Kunena 5.0
	 */
	public function _getTopHits($limit = 0)
	{
	}

	/**
	 * @param $view
	 * @param $params
	 * @since       Kunena 5.0
	 */
	public function showProfile($view, &$params)
	{
		$userid = $view->profile->userid;

		$user = FD::user($userid);

		$gender = $user->getFieldData('GENDER');

		if (!empty($gender))
		{
			$view->profile->gender = $gender;
		}

		$data     = $user->getFieldData('BIRTHDAY');
		$json     = FD::json();
		$birthday = null;

		// Legacy
		if (isset($data['date']) && $json->isJsonString($data['date']) && !$birthday)
		{
			$birthday = $this->getLegacyDate($data['date']);
		}

		// Legacy
		if ($json->isJsonString($data) && !$birthday)
		{
			$birthday = $this->getLegacyDate($data);
		}

		// New format
		if (isset($data['date']) && !$birthday)
		{
			$birthday = FD::date($data['date']);
		}

		if (!is_null($birthday))
		{
			$view->profile->birthdate = $birthday->format('Y-m-d');
		}
	}

	/**
	 * @param $birthday
	 *
	 * @return mixed
	 * @since       Kunena 5.0
	 */
	public function getLegacyDate($birthday)
	{
		$birthday = json_decode($birthday);
		$birthday = FD::date($birthday->day . '-' . $birthday->month . '-' . $birthday->year);

		return $birthday;
	}

	/**
	 * @param      $userid
	 * @param   bool $xhtml xhtml
	 *
	 * @return mixed
	 * @since       Kunena 5.0
	 *
	 */
	public function getEditProfileURL($userid, $xhtml = true)
	{
		$options = array('layout' => 'edit');
		$url     = FRoute::profile($options, $xhtml);

		return $url;
	}

	/**
	 * Return username of user
	 *
	 * @param   integer $userid userid
	 * @param   bool    $xhtml  xhtml
	 *
	 * @since Kunena 5.2
	 * @return string
	 */
	public function getProfileName($user, $visitorname = '', $escape = true)
	{
		return FD::user($user->id)->username;
	}
}
