// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @package   customfield_multiselect
 * @copyright  2020 CALL Learning 2020 - Laurent David <laurent@call-learning.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/str'],
    function ($, Str) {
        return {
            init: function (buttonid, autocompleteid) {
                $('#' + buttonid).click(function () {
                    // Find the select element
                    var select = $('#' + autocompleteid);
                    
                    if (select.length > 0) {
                        // Clear all selected options
                        select.find('option').prop('selected', false);
                        
                        // Find the autocomplete selection container within the same parent
                        var selectionElement = select.closest('.felement').find('.form-autocomplete-selection');
                        
                        if (selectionElement.length > 0) {
                            // Clear the visual selection elements
                            selectionElement.find('[role=option]').remove();
                            
                            // Add the "no selection" text using Moodle's core language string
                            Str.get_string('noselection', 'form').then(function(noselectiontext) {
                                if (selectionElement.find('.m-1').length === 0) {
                                    selectionElement.append('<span class="m-1 h-5">' + noselectiontext + '</span>');
                                }
                            });
                        }
                        
                        // Trigger change event on the select
                        select[0].dispatchEvent(new Event('change', {bubbles: true}));
                    }
                });
            }
        };
    });
