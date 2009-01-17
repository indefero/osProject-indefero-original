<?php
/* -*- tab-width: 4; indent-tabs-mode: nil; c-basic-offset: 4 -*- */
/*
# ***** BEGIN LICENSE BLOCK *****
# This file is part of InDefero, an open source project management application.
# Copyright (C) 2008 Céondo Ltd and contributors.
#
# InDefero is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# InDefero is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
#
# ***** END LICENSE BLOCK ***** */

/**
 * Synchronize the SSH keys with InDefero.
 */
class IDF_Plugin_SyncGit_Cron
{
    /**
     * Template for the SSH key.
     */
    public $template = 'command="%s %s",no-port-forwarding,no-X11-forwarding,'
        .'no-agent-forwarding,no-pty %s';

    /**
     * Synchronize.
     */
    public static function sync()
    {
        $template = Pluf::factory(__CLASS__)->template;
        $keys = Pluf::factory('IDF_Key')->getList(array('view'=>'join_user'));
        $cmd = Pluf::f('idf_plugin_syncgit_path_gitserve', '/bin/false');
        $authorized_keys = Pluf::f('idf_plugin_syncgit_path_authorized_keys', false);
        if (false == $authorized_keys) {
            throw new Pluf_Exception_SettingError('Setting git_path_authorized_keys not set.');
        }
        if (!is_writable($authorized_keys)) {
            throw new Exception('Cannot create file: '.$authorized_keys);
        }
        $out = '';
        foreach ($keys as $key) {
            if (strlen($key->content) > 40 // minimal check
                and preg_match('/^[a-zA-Z][a-zA-Z0-9_.-]*(@[a-zA-Z][a-zA-Z0-9.-]*)?$/', $key->login)) {
                $content = str_replace("\n", '', $key->content);
                $out .= sprintf($template, $cmd, $key->login, $content)."\n";
            }
        }
        file_put_contents($authorized_keys, $out, LOCK_EX);        
    }
}