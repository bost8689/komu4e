function show_hide(obj_id) {
doc=document.getElementById(obj_id);
console.log(doc);
if(doc.style.display == "none") doc.style.display = "block";
else doc.style.display = "none"
}
