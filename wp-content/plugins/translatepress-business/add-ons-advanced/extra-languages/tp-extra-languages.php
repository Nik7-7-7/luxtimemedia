<?php
/*
TranslatePress - Extra Languages Add-on
License: GPL2

== Copyright ==
Copyright 2017 Cozmoslabs (www.cozmoslabs.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/



function trp_in_el_run(){
    require_once plugin_dir_path( __FILE__ ) . 'class-extra-languages.php';
    if ( class_exists( 'TRP_Translate_Press' ) ) {
        new TRP_IN_Extra_Languages();
    }
}
add_action( 'plugins_loaded', 'trp_in_el_run', 0 );