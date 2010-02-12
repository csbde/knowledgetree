/**
 * jQuery Select Image Plugin
 *
 * @author Charl Mert <charl@knowledgetree.com>
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2009, 2010 KnowledgeTree Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco, 
 * California 94120-7775, or email info@knowledgetree.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * KnowledgeTree" logo and retain the original copyright notice. If the display of the 
 * logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices
 * must display the words "Powered by KnowledgeTree" and retain the original 
 * copyright notice.
 * Contributor( s): ______________________________________
 */

jQuery.fn.selectImage = function() {
  return this.each(function(){

    var opt = jQuery.extend({
      passBackNamespace : 'selectimage', //TODO: Let plugin detect parent form and produce the hidden inputs. For now the user has to place the predefined hidden inputs onto the form manually.
      multiselect : false,
      hover : '#e2e2e2',
      click : '#f2943a', //Orange
      background : 'white',
      selected : ''
    }, opt);

    var containerId = this.id;
    var custId = 0;

    //Setting up border
    jQuery('#' + containerId + ' img'   ).css('border', '10px solid ' + opt.background);

    jQuery('#' + containerId + ' img').each(function(){
      //Assigning custom id's to attach event handlers to
      if (this.id == '') {
        this.id = 'select_image_img_' + custId;
        custId++;
      }

      var toggle = 1;

      //Click to select
      jQuery('#' + this.id).click(function(){
          opt.selected = this.id;
          jQuery('#' + opt.passBackNamespace + '_src').val(this.src);
          jQuery('#' + opt.passBackNamespace + '_alt').val(this.alt);
          jQuery('#' + opt.passBackNamespace + '_title').val(this.title);

          //Resetting background:
          jQuery('#' + containerId + ' img'   ).css('border', '10px solid ' + opt.background);
          jQuery('#' + this.id).css('border', '10px solid ' + opt.click);
      })

        //Hover Effect
        jQuery('#' + this.id).mouseenter(function(){
          if (this.id != opt.selected) {
            jQuery('#' + this.id).css('border', '10px solid ' + opt.hover).show("slow");
          }
        })

        jQuery('#' + this.id).mouseleave(function(){
          if (this.id != opt.selected) {
            jQuery('#' + this.id).css('border', '10px solid ' + opt.background).show("slow");
          }
        })

    });
  });
};

jQuery.log = function(message) {
  if(window.console) {
     console.debug(message);
  } else {
     alert(message);
  }
};