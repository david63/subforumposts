<?php
/**
*
* @package Credits Page Extension
* @copyright (c) 2019 david63
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace david63\subforumposts\event;

/**
* @ignore
*/
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use phpbb\db\driver\driver_interface;

/**
* Event listener
*/
class listener implements EventSubscriberInterface
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var string phpBB tables */
	protected $tables;

	/**
	* Constructor for listener
	*
	* @param \phpbb_db_driver	$db			The db connection
	* @param array				$tables		phpBB db tables
	*
	* @return \david63\subforumposts\event\listener
	* @access public
	*/
	public function __construct(driver_interface $db, $tables)
	{
		$this->db		= $db;
		$this->tables	= $tables;
	}

	/**
	* Assign functions defined in this class to event listeners in the core
	*
	* @return array
	* @static
	* @access public
	*/
	static public function getSubscribedEvents()
	{
		return array(
			'core.display_forums_modify_template_vars'	=> 'sub_forum_posts',
		);
	}

	public function sub_forum_posts($event)
	{
		$subforums_row	= $event['subforums_row'];
		$forum_data 	= array();

		// Get fora post counts
		$sql = 'SELECT forum_id, forum_posts_approved
			FROM ' . $this->tables['forums'];

		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$forum_data[] = $row['forum_posts_approved'];
		}

		$this->db->sql_freeresult($result);

		foreach ($subforums_row as $key => $subforum)
		{
			if (is_array($subforum))
			{
				if (array_key_exists('SUBFORUM_NAME', $subforum))
				{
					$forum_id = ltrim(strstr($subforum['U_SUBFORUM'], '='), '=') -1;
					$subforum['SUBFORUM_NAME'] = $subforum['SUBFORUM_NAME'] . ' [' . $forum_data[$forum_id] . ']';
				}
			}
			$subforums_row[$key] = $subforum;
		}

		$event['subforums_row'] = $subforums_row;
	}

}
