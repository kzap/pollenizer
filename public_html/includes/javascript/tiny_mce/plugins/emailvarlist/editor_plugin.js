(function(){tinymce.create('tinymce.plugins.EmailVarList',{init:function(ed,url){},createControl:function(n,cm){switch(n){case"emailvarlist":var mlb=cm.createListBox("mylistbox",{title:"Email Vars List",onselect:function(v){if(v){tinyMCE.activeEditor.execCommand("mceInsertContent",false,v)}}});mlb.add("--Member Variables--","");mlb.add("First Name","{members_firstname}");mlb.add("Last Name","{members_lastname}");mlb.add("Email","{members_email}");mlb.add("Address 1","{members_address01}");mlb.add("Address 2","{members_address02}");mlb.add("City","{members_city}");mlb.add("State","{members_state}");mlb.add("Country","{members_country}");mlb.add("ZIP/Post Code","{members_zip_code}");mlb.add("Phone #","{members_phone}");return mlb}return null},getInfo:function(){return{longname:'Example plugin',author:'Some author',authorurl:'http://tinymce.moxiecode.com',infourl:'http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/example',version:"1.0"}}});tinymce.PluginManager.add('emailvarlist',tinymce.plugins.EmailVarList)})();