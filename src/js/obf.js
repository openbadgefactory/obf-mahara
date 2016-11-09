/* global $j */

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

    var userid = -1;
    var options = {};

    var add_backpack_tab = function() {
        var subnav = get_subnav();
        console.log(subnav);
        if (subnav === null) {
            return;
        }

        var url = window.config.wwwroot + 'interaction/obf/profile.php?user=' + userid;
        var link = $j('<a></a>').text(options.lang.openbadgessettings).attr('href', url);
        var listelement = $j('<li></li>').append(link).addClass('backpack');

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
        var btn = $j('<button />')
                .addClass('btn btn-primary btn-sm')
                .attr('type', 'button')
                .attr('id', 'issue-to-all')
                .text(get_string('issuetoall'));

        btn.click(function () {
            $j('select#users_potential option').attr('selected', true);
            users_moveopts('potential', 'members');
        });

        $j('td.lrbuttons').append(btn);
    };

    var get_subnav = function () {
        // HACK. In default theme without dropdown navigation the sub navigation
        // is found under #sub-nav ul.nav. However when the dropdown navigation
        // is enabled, the account settings links are located in
        // #main ul.nav-inpage. If the theme changes the navigation structure
        // from this, the links won't appear.
        var possible_subnavs = ['#sub-nav ul.nav', '#main ul.nav-inpage', '#sub-nav ul:first'];
        var subnav = null;

        for (var i = 0; i < possible_subnavs.length; i++) {
            subnav = $j(possible_subnavs[i]);

            if (subnav.size() > 0) {
                return subnav.first();
            }
        }

        return null;
    };

    return {
        init_profile: function(uid, opts) {
            options = opts;
            userid = uid;
            add_backpack_tab();
        },

        init_categories: function() {
            $j('ul.badge-categories').on('click', 'button', toggle_category);
            $j('button.badge-reset-filter').click(reset_category_filter);
        },

        init_issuance_page: function() {
            create_issue_to_all_button();
        },

        connect_to_backpack: function(evt, email) {
            evt.preventDefault();
            var assertion_callback = function(assertion) {
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
            };
            
            if (email !== undefined && typeof email === 'string') {
                assertion_callback({email: email, localemail: true});
            } else {
                navigator.id.get(assertion_callback);
            }
        },

        select_backpack_tab: function () {
            var nav = get_subnav();

            if (nav !== null) {
                nav.children('li.backpack').addClass('active');
            }
        }
    };
})();