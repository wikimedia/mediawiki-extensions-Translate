function appendFromSelect(selectid,targetid) {
  var select = document.getElementById(selectid);
  var target = document.getElementById(targetid);
  if(!target || !select) return
  var atxt = select.options[select.selectedIndex].value;
  if(!atxt) return
  if(target.value.replace(/[\s\t\n]/ig,'') != '') atxt = ', ' + atxt;
  target.value += atxt;
}