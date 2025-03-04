<?php
/* vim: ts=4
 +-------------------------------------------------------------------------+
 | Copyright (C) 2021 The Cacti Group, Inc.                                |
 | Copyright (C) 2015-2020 Petr Macek                                      |
 |                                                                         |
 | This program is free software; you can redistribute it and/or           |
 | modify it under the terms of the GNU General Public License             |
 | as published by the Free Software Foundation; either version 2          |
 | of the License, or (at your option) any later version.                  |
 |                                                                         |
 | This program is distributed in the hope that it will be useful,         |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of          |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the           |
 | GNU General Public License for more details.                            |
 +-------------------------------------------------------------------------+
 | Cacti: The Complete RRDtool-based Graphing Solution                     |
 +-------------------------------------------------------------------------+
 | This code is designed, written, and maintained by the Cacti Group. See  |
 | about.php and/or the AUTHORS file for specific developer information.   |
 +-------------------------------------------------------------------------+
 | https://github.com/xmacan/                                              |
 | http://www.cacti.net/                                                   |
 +-------------------------------------------------------------------------+
*/

function intropage_drop_database() {
	db_execute("DELETE FROM settings WHERE name LIKE 'intropage_%'");
	db_execute('DROP TABLE IF EXISTS plugin_intropage_user_setting');
	db_execute('DROP TABLE IF EXISTS plugin_intropage_panel');
	db_execute('UPDATE user_auth SET login_opts = 1 WHERE login_opts > 3');

	db_execute('ALTER TABLE user_auth DROP COLUMN IF EXISTS intropage_analyse_log');
	db_execute('ALTER TABLE user_auth DROP COLUMN IF EXISTS intropage_analyse_login');
	db_execute('ALTER TABLE user_auth DROP COLUMN IF EXISTS intropage_thold_event');
	db_execute('ALTER TABLE user_auth DROP COLUMN IF EXISTS intropage_analyse_db');
	db_execute('ALTER TABLE user_auth DROP COLUMN IF EXISTS intropage_analyse_tree_host_graph');
	db_execute('ALTER TABLE user_auth DROP COLUMN IF EXISTS intropage_trend');
	db_execute('ALTER TABLE user_auth DROP COLUMN IF EXISTS intropage_extrem');
	db_execute('ALTER TABLE user_auth DROP COLUMN IF EXISTS intropage_ntp');
	db_execute('ALTER TABLE user_auth DROP COLUMN IF EXISTS intropage_poller_info');
	db_execute('ALTER TABLE user_auth DROP COLUMN IF EXISTS intropage_poller_stat');
	db_execute('ALTER TABLE user_auth DROP COLUMN IF EXISTS intropage_graph_host');
	db_execute('ALTER TABLE user_auth DROP COLUMN IF EXISTS intropage_graph_thold');
	db_execute('ALTER TABLE user_auth DROP COLUMN IF EXISTS intropage_graph_data_source');
	db_execute('ALTER TABLE user_auth DROP COLUMN IF EXISTS intropage_graph_host_template');
	db_execute('ALTER TABLE user_auth DROP COLUMN IF EXISTS intropage_cpuload');
	db_execute('ALTER TABLE user_auth DROP COLUMN IF EXISTS intropage_cpu');
	db_execute('ALTER TABLE user_auth DROP COLUMN IF EXISTS intropage_mactrack');
	db_execute('ALTER TABLE user_auth DROP COLUMN IF EXISTS intropage_mactrack_sites');
	db_execute('ALTER TABLE user_auth DROP COLUMN IF EXISTS intropage_top5_ping');
	db_execute('ALTER TABLE user_auth DROP COLUMN IF EXISTS intropage_top5_availability');
	db_execute('ALTER TABLE user_auth DROP COLUMN IF EXISTS intropage_top5_polltime');
	db_execute('ALTER TABLE user_auth DROP COLUMN IF EXISTS intropage_top5_pollratio');
	db_execute('ALTER TABLE user_auth DROP COLUMN IF EXISTS intropage_info');
	db_execute('ALTER TABLE user_auth DROP COLUMN IF EXISTS intropage_boost');
	db_execute('ALTER TABLE user_auth DROP COLUMN IF EXISTS intropage_favourite_graph');
	db_execute('ALTER TABLE user_auth DROP COLUMN IF EXISTS intropage_plugin_syslog');

	// version 2
	db_execute('DROP TABLE IF EXISTS plugin_intropage_panel_definition');
	db_execute('DROP TABLE IF EXISTS plugin_intropage_panel_data');
	db_execute('DROP TABLE IF EXISTS plugin_intropage_panel_dashboard');
	db_execute('DROP TABLE IF EXISTS plugin_intropage_trends');
	db_execute('DROP TABLE IF EXISTS plugin_intropage_user_auth');
	db_execute('DROP TABLE IF EXISTS plugin_intropage_dashboard');
}

function intropage_initialize_database() {
	global $config;

	include_once($config['base_path'] . '/plugins/intropage/include/functions.php');

	$data              = array();
	$data['columns'][] = array('name' => 'cur_timestamp', 'type' => 'timestamp');
	$data['columns'][] = array('name' => 'name', 'type' => 'varchar(50)', 'NULL' => false, 'default' => '0');
	$data['columns'][] = array('name' => 'user_id', 'type' => 'int(11)', 'NULL' => false, 'default' => '0');
	$data['columns'][] = array('name' => 'value', 'type' => 'varchar(250)', 'NULL' => true, 'default' => null);
	$data['type']      = 'InnoDB';
	$data['comment']   = 'Intropage trends';
	api_plugin_db_table_create('intropage', 'plugin_intropage_trends', $data);

	db_execute('ALTER TABLE plugin_intropage_trends
		MODIFY COLUMN cur_timestamp TIMESTAMP default current_timestamp ON UPDATE current_timestamp');

	$data              = array();
	$data['columns'][] = array('name' => 'panel_id', 'type' => 'varchar(50)', 'NULL' => false);
	$data['columns'][] = array('name' => 'name', 'type' => 'varchar(30)', 'NULL' => false, 'default' => '');
	$data['columns'][] = array('name' => 'level', 'type' => 'tinyint', 'unsigned' => true, 'default' => 0);
	$data['columns'][] = array('name' => 'class', 'type' => 'varchar(30)', 'NULL' => false, 'default' => '');
	$data['columns'][] = array('name' => 'priority', 'type' => 'tinyint', 'unsigned' => true, 'default' => 0);
	$data['columns'][] = array('name' => 'alarm', 'type' => 'varchar(10)', 'NULL' => false, 'default' => '');
	$data['columns'][] = array('name' => 'requires', 'type' => 'varchar(128)', 'NULL' => false, 'default' => '');
	$data['columns'][] = array('name' => 'update_func', 'type' => 'varchar(30)', 'NULL' => false, 'default' => '');
	$data['columns'][] = array('name' => 'details_func', 'type' => 'varchar(30)', 'NULL' => false, 'default' => '');
	$data['columns'][] = array('name' => 'trends_func', 'type' => 'varchar(30)', 'NULL' => false, 'default' => '');
	$data['columns'][] = array('name' => 'refresh', 'type' => 'int(10)', 'unsigned' => true, 'default' => '3600');
	$data['columns'][] = array('name' => 'trefresh', 'type' => 'int(10)', 'unsigned' => true, 'default' => '3600');
	$data['columns'][] = array('name' => 'description', 'type' => 'varchar(200)', 'default' => '', 'NULL' => true);

	$data['type']      = 'InnoDB';
	$data['primary']   = 'panel_id';
	$data['comment']   = 'Panels Definitions of panels in panel library';
	api_plugin_db_table_create('intropage', 'plugin_intropage_panel_definition', $data);

	$data              = array();
	$data['columns'][] = array('name' => 'panel_id', 'type' => 'int(11)', 'NULL' => false);
	$data['columns'][] = array('name' => 'user_id', 'type' => 'int(11)', 'NULL' => false);
	$data['columns'][] = array('name' => 'dashboard_id', 'type' => 'int(11)', 'NULL' => false);
	$data['type']      = 'InnoDB';
	$data['primary']   = 'panel_id`, `user_id`, `dashboard_id';
	$data['comment']   = 'panel x dashboard dependency';
	api_plugin_db_table_create('intropage', 'plugin_intropage_panel_dashboard', $data);

	$data              = array();
	$data['columns'][] = array('name' => 'id', 'type' => 'int(11)', 'NULL' => false, 'auto_increment' => true);
	$data['columns'][] = array('name' => 'panel_id', 'type' => 'varchar(50)', 'NULL' => false);
	$data['columns'][] = array('name' => 'user_id', 'type' => 'int(11)', 'NULL' => false);
	$data['columns'][] = array('name' => 'last_update', 'type' => 'timestamp', 'default' => 'CURRENT_TIMESTAMP', 'NULL' => false);
	$data['columns'][] = array('name' => 'last_trend_update', 'type' => 'timestamp', 'NULL' => false);
	$data['columns'][] = array('name' => 'data', 'type' => 'text', 'NULL' => true);
	$data['columns'][] = array('name' => 'priority', 'type' => 'int(3)', 'default' => '30', 'NULL' => false);
	$data['columns'][] = array('name' => 'alarm', 'type' => "enum('red','green','yellow','grey')", 'default' => 'green', 'NULL' => false);
	$data['columns'][] = array('name' => 'refresh_interval', 'type' => 'int(9)', 'default' => '3600', 'NULL' => false);
	$data['columns'][] = array('name' => 'trend_interval', 'type' => 'int(9)', 'default' => '300', 'NULL' => false);
	$data['columns'][] = array('name' => 'fav_graph_id', 'type' => 'int(11)', 'NULL' => true);
	$data['columns'][] = array('name' => 'fav_graph_timespan', 'type' => 'int(2)', 'default' => '1', 'NULL' => false);

	$data['type']      = 'InnoDB';
	$data['primary']   = 'id';
	$data['comment']   = 'panel data';
	api_plugin_db_table_create('intropage', 'plugin_intropage_panel_data', $data);

	$panels = initialize_panel_library();

	update_registered_panels($panels);

	foreach($panels as $panel_id => $panel) {
		if ($panel['level'] == 0) {
			db_execute_prepared('INSERT INTO plugin_intropage_panel_data
				(panel_id, user_id, priority, alarm, refresh_interval, trend_interval)
				VALUES(?, "0", ?, ?, ?, ?)',
				array($panel_id, $panel['priority'], $panel['alarm'], $panel['refresh'], $panel['trefresh']));
		} else {
			db_execute_prepared('INSERT INTO plugin_intropage_panel_data
				(panel_id, user_id, priority, alarm, refresh_interval, trend_interval)
				VALUES(?, ?, ?, ?, ?, ?)',
				array($panel_id, $_SESSION['sess_user_id'], $panel['priority'], $panel['alarm'], $panel['refresh'], $panel['trefresh']));
		}
	}

	$data              = array();
	$data['columns'][] = array('name' => 'user_id', 'type' => 'int(11)', 'NULL' => false);
	$data['columns'][] = array('name' => 'login_opts', 'type' => 'tinyint(1)', 'NULL' => false, 'default' => '0');
	$data['columns'][] = array('name' => 'permissions', 'type' => 'blob', 'NULL' => false, 'default' => '');
	$data['type']      = 'InnoDB';
	$data['primary']   = 'user_id';
	$data['comment']   = 'authorization';
	api_plugin_db_table_create('intropage', 'plugin_intropage_user_auth', $data);

	$permissions = array();
	foreach($panels as $panel_id => $panel) {
		$permissions[$panel_id] = 'on';
	}

	$permissions['favourite_graph'] = 'on';

	db_execute_prepared('INSERT INTO plugin_intropage_user_auth
		(user_id, permissions)
		VALUES (?, ?)',
		array($_SESSION['sess_user_id'], json_encode($permissions)));

	$data              = array();
	$data['columns'][] = array('name' => 'user_id', 'type' => 'int(11)', 'NULL' => false);
	$data['columns'][] = array('name' => 'dashboard_id', 'type' => 'int(11)', 'NULL' => false);
	$data['columns'][] = array('name' => 'name', 'type' => 'varchar(30)', 'NULL' => true);
	$data['type']      = 'InnoDB';
	$data['comment']   = 'panel x dashboard name';
	api_plugin_db_table_create('intropage', 'plugin_intropage_dashboard', $data);

	db_execute('ALTER TABLE plugin_intropage_dashboard ADD PRIMARY KEY (user_id, dashboard_id)');
}

function intropage_upgrade_database() {
	global $config;

	// If action need to be done for upgrade, add it.
	$info = parse_ini_file($config['base_path'] . '/plugins/intropage/INFO', true);
	$info = $info['info'];

	$current = $info['version'];
	$oldv    = db_fetch_cell('SELECT version FROM plugin_config WHERE directory = "intropage"');

	if (!cacti_version_compare($oldv, $current, '=')) {
		if (cacti_version_compare($oldv,'0.9','<')) {
			db_execute('UPDATE plugin_hooks
				SET function = "intropage_config_form", file = "include/settings.php"
				WHERE name = "intropage"
				AND hook = "config_form"');

			db_execute('UPDATE plugin_hooks
				SET function = "intropage_config_settings", file = "include/settings.php"
				WHERE name = "intropage"
				AND hook = "config_settings"');

			db_execute('UPDATE plugin_hooks
				SET function = "intropage_show_tab", file = "include/tab.php"
				WHERE name = "intropage"
				AND hook = "top_header_tabs"');

			db_execute('UPDATE plugin_hooks
				SET function = "intropage_show_tab", file = "include/tab.php"
				WHERE name = "intropage"
				AND hook = "top_graph_header_tabs"');

			db_execute('UPDATE plugin_hooks
				SET function = "intropage_login_options_navigate", file = "include/settings.php"
				WHERE name = "intropage"
				AND hook = "login_options_navigate"');

			db_execute('UPDATE plugin_hooks
				SET function ="intropage_console_after", file = "include/settings.php"
				WHERE name = "intropage"
				AND hook = "console_after"');

			db_execute('UPDATE user_auth
				SET login_opts = 1
				WHERE login_opts IN (4,5)');
		}

		if (cacti_version_compare($oldv,'2.0.2', '<')) {
			// a lot of changes, so:
			intropage_drop_database();
			intropage_initialize_database();

			api_plugin_register_hook('intropage', 'user_admin_tab', 'intropage_user_admin_tab', 'includes/settings.php');
			api_plugin_register_hook('intropage', 'user_admin_run_action', 'intropage_user_admin_run_action', 'includes/settings.php');
			api_plugin_register_hook('intropage', 'user_admin_user_save', 'intropage_user_admin_user_save', 'includes/settings.php');
			api_plugin_register_hook('intropage', 'user_remove', 'intropage_user_remove', 'setup.php');
		}

		if (cacti_version_compare($oldv, '2.0.4', '<=')) {
			db_add_column('plugin_intropage_user_auth',
				array(
					'name'    => 'webseer',
					'type'    => 'char(2)',
					'NULL'    => false,
					'default' => 'on'
				)
			);
		}

		if (cacti_version_compare($oldv, '2.0.5', '<=')) {
			$data = array();

			$data['columns'][] = array('name' => 'user_id', 'type' => 'int(11)', 'NULL' => false);
			$data['columns'][] = array('name' => 'dashboard_id', 'type' => 'int(11)', 'NULL' => false);
			$data['columns'][] = array('name' => 'name', 'type' => 'varchar(30)', 'NULL' => true);
			$data['type']      = 'InnoDB';
			$data['comment']   = 'panel x dashboard name';

			api_plugin_db_table_create('intropage', 'plugin_intropage_dashboard', $data);

			db_execute('ALTER TABLE plugin_intropage_dashboard ADD PRIMARY KEY (user_id, dashboard_id)');

			db_add_column('plugin_intropage_panel_data',
				array(
					'name'    => 'refresh_interval',
					'type'    => 'int(9)',
					'NULL'    => false,
					'default' => '3600',
					'after'   => 'alarm'
				)
			);
		}

		if (cacti_version_compare($oldv, '3.0.0', '<=')) {
			include_once($config['base_path'] . '/plugins/intropage/include/functions.php');

			if (db_column_exists('plugin_intropage_panel_definition', 'file')) {
				db_execute('ALTER TABLE plugin_intropage_panel_definition
					DROP COLUMN `file`,
					DROP COLUMN `has_detail`,
					CHANGE COLUMN refresh_interval refresh int(10) unsigned NOT NULL default "30",
					ADD COLUMN name varchar(30) NOT NULL default "" AFTER panel_id,
					ADD COLUMN level int(10) unsigned NOT NULL default "0" AFTER name,
					ADD COLUMN alarm varchar(10) NOT NULL default "" AFTER priority,
					ADD COLUMN requires varchar(128) NOT NULL default "" AFTER alarm,
					ADD COLUMN update_func varchar(30) NOT NULL default "" AFTER requires,
					ADD COLUMN details_func varchar(30) NOT NULL default "" AFTER update_func,
					ADD COLUMN class varchar(30) NOT NULL default "" AFTER level,
					ADD COLUMN trends_func varchar(30) NOT NULL default "" AFTER details_func');
			}

			if (!db_column_exists('plugin_intropage_user_auth', 'permissions')) {
				db_execute('ALTER TABLE plugin_intropage_user_auth
					ADD COLUMN permissions BLOB default "" AFTER login_opts');

				$panels      = initialize_panel_library();
				$permissions = db_fetch_assoc('SELECT * FROM plugin_intropage_user_auth');

				$permissions['favourite_graph'] = 'on';

				if (cacti_sizeof($permissions)) {
					foreach($permissions as $p) {
						$user = $p['user_id'];
						$opts = $p['login_opts'];

						unset($p['user_id']);
						unset($p['login_opts']);

						// Remove panels that are no longer published
						foreach($p as $panel_id => $data) {
							if (!isset($panels[$panel_id])) {
								unset($p[$panel_id]);
							}
						}

						$perms = json_encode($p);

						db_execute_prepared('UPDATE plugin_intropage_user_auth
							SET permissions = ?
							WHERE user_id = ?',
							array($perms, $user));
					}
				}

				$columns = db_fetch_assoc('SHOW COLUMNS FROM plugin_intropage_user_auth');

				foreach($columns as $c) {
					switch($c['Field']) {
						case 'user_id':
						case 'login_opts':
						case 'permissions':
							break;
						default:
							db_execute('ALTER TABLE plugin_intropage_user_auth DROP COLUMN ' . $c['Field']);
							break;
					}
				}
			}

			db_execute('UPDATE plugin_intropage_panel_data
				SET alarm = "grey" WHERE alarm = "gray"');

			db_execute('ALTER TABLE plugin_intropage_panel_dashboard
				ADD PRIMARY KEY (panel_id, user_id, dashboard_id)');

			db_execute('ALTER TABLE plugin_intropage_panel_data
				MODIFY COLUMN `alarm` enum("red","green","yellow","grey") COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT "green"');
		}

		if (cacti_version_compare($oldv, '3.0.3', '<=')) {
			if (!db_column_exists('plugin_intropage_panel_data', 'trend_interval')) {
				db_execute('ALTER TABLE plugin_intropage_panel_data
					ADD COLUMN trend_interval INT(9) UNSIGNED NOT NULL default "300" AFTER refresh_interval');
			}

			if (!db_column_exists('plugin_intropage_panel_data', 'last_trend_update')) {
				db_execute('ALTER TABLE plugin_intropage_panel_data
					ADD COLUMN last_trend_update TIMESTAMP NOT NULL default CURRENT_TIMESTAMP AFTER last_update');
			}

			if (!db_column_exists('plugin_intropage_panel_definition', 'trefresh')) {
				db_execute('ALTER TABLE plugin_intropage_panel_definition
					ADD COLUMN trefresh INT(10) UNSIGNED default "3600" AFTER refresh');
			}
		}

		if (cacti_version_compare($oldv, '4.0.1', '<=')) {
			db_execute('ALTER TABLE plugin_intropage_panel_data
				CHANGE last_update last_update timestamp NOT NULL default current_timestamp');
		}

		// Set the new version
		db_execute_prepared("UPDATE plugin_config
			SET version = ?, author = ?, webpage = ?
			WHERE directory = 'intropage'",
			array(
				$info['version'],
				$info['author'],
				$info['homepage']
			)
		);

		api_plugin_register_hook('intropage', 'page_head', 'intropage_page_head', 'setup.php', 1);
	}
}

