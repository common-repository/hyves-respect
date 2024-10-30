function hyvesrespect_changePreview(){
    // shows preview of button
    var ypos=0;
    switch(document.getElementById('hyvesrespect_stylelayout').value){
        case 'vertical': ypos=0; break;
        case 'nocount': ypos=-200; break;
        default: ypos=-100; // standard
    } // switch
	 document.getElementById('hyvesrespect_preview').style.backgroundPosition='0px '+ypos+'px';
}
// initialize onload
jQuery(document).ready(function($) {
    hyvesrespect_changePreview();
});
