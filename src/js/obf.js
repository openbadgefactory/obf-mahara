/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage interaction-obf
 * @author     Discendum Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) Discendum Ltd http://discendum.com
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */
var Obf = (function() {
    "use strict";

    var groupid = -1;
    var userid = -1;
    var options = {};

    var add_issuance_tab = function() {
        var subnav = get_page_tabs();
        
        if (!subnav) {
            return;
        }
        
        var url = window.config.wwwroot + 'interaction/obf/group.php?id=' + groupid;
        var link = $j('<a></a>').text(options.lang.badges).attr('href', url);
        var listelement = $j('<li></li>').append(link).addClass('badges');

        subnav.append(listelement);
    };

    var add_backpack_tab = function() {
        var subnav = get_subnav();
        
        if (!subnav) {
            return;
        }
        
        var url = window.config.wwwroot + 'interaction/obf/profile.php?user=' + userid;
        var link = $j('<a></a>').text(options.lang.backpacksettings).attr('href', url);
        var listelement = $j('<li></li>').append(link).addClass('backpack');

        subnav.append(listelement);
    };
    
    var add_supervisor_tab = function() {
        var subnav = get_subnav();
        
        if (!subnav) {
            return;
        }
        
        var url = window.config.wwwroot + 'interaction/obf/supervisor.php';
        var link = $j('<a></a>').text(options.lang.badges).attr('href', url);
        var listelement = $j('<li></li>').append(link).addClass('badges');
        
        subnav.append(listelement);
    };

    var toggle_category = function(evt) {
        $j(this).toggleClass('active');
        apply_category_change();
    };

    var reset_category_filter = function() {
        $j('ul.badge-categories button').removeClass('active');
        apply_category_change();
    };

    var apply_category_change = function() {
        var selected_buttons = $j('ul.badge-categories button.active');
        var selected_categories = selected_buttons.map(function() {
            return $j(this).text();
        });

        $j('ul#badges li').each(function(index) {
            var badge_categories = $j.parseJSON($j(this).attr('data-categories'));
            var show_badge = true;

            // Show all badges if none of the categories is selected.
            if (selected_categories.length === 0) {
                show_badge = true;
            }
            else {
                $j.each(selected_categories, function(index, category) {
                    if ($j.inArray(category, badge_categories) === -1) {
                        show_badge = false;
                    }
                });
            }

            $j(this)[show_badge ? 'fadeIn' : 'fadeOut']('fast');
        });
    };
    
    var create_issue_to_all_button = function () {
        var btn = $j('<button></button>').attr('type', 'button').text(get_string('issuetoall'));
        btn.click(function () {
            $j('select#users_potential option').attr('selected', true);
            users_moveopts('potential', 'members');
        });
        
        $j('td.lrbuttons').append(btn);
    };

    var get_subnav = function () {
        return ($j('#sub-nav > ul').size() > 0 ? $j('#sub-nav > ul').first() : get_page_tabs());
    };
    
    var get_page_tabs = function () {
        return ($j('ul.in-page-tabs').size() > 0 ? $j('ul.in-page-tabs').first() : null);
    };

    return {
        init_group: function(gid, opts) {
            options = opts;
            groupid = gid;
            add_issuance_tab();
        }

        , init_profile: function(uid, opts) {
            options = opts;
            userid = uid;
            add_backpack_tab();
        }

        , init_categories: function() {
            $j('ul.badge-categories').on('click', 'button', toggle_category);
            $j('button.badge-reset-filter').click(reset_category_filter);
        }
        
        , init_supervisor: function (opts) {
            options = opts;
            add_supervisor_tab();
        }

        , init_issuance_page: function() {            
            create_issue_to_all_button();
            
            $j('#badge-issue-form-wrapper .tabswrap ul').on('click', 'li', function (evt) {
                evt.preventDefault();
                
                $j(this).siblings().removeClass('current-tab');
                $j(this).siblings().children('a').removeClass('current-tab');
                $j(this).addClass('current-tab');
                $j(this).children('a').addClass('current-tab');
                
                var tabname = $j(this).data('tab');
                var content = $j('#badge-issue-form-wrapper .subpage div[data-tab-content="' + tabname +'"]');
                
                content.siblings().hide();
                content.show();
            });
        }

        , connect_to_backpack: function(evt) {
            evt.preventDefault();

            navigator.id.get(function(assertion) {
                $j('#assertion-error').hide();
                if (assertion !== null) {
                    $j.getJSON(window.config.wwwroot + 'interaction/obf/authenticate.json.php', {
                        assertion: assertion},
                    function(res, status, xhr) {
                        if (res.error === true) {
                            $j('#assertion-error').text(res.message).show();
                        }
                        else {
                            window.location.reload();
                        }
                    });
                }
            });
        }
        
        , select_backpack_tab: function () {
            get_subnav().children('li.backpack').addClass('current-tab selected').children('a').addClass('current-tab');
        }
    };
})();