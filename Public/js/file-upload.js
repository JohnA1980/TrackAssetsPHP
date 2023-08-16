
function fileDragHover(e) 
{
	e.stopPropagation();
	e.preventDefault();
	e.target.className = (e.type == "dragover" ? "hover" : "");
}

function fileUploadHandler(input, e, token, type, callback)
{
	// cancel event and hover styling
	fileDragHover(e);

	// fetch FileList object
	var files = e.target.files || e.dataTransfer.files;

	// process all File objects
	for (var i = 0, f; f = files[i]; i++) 
	{
		// create progress bar
		var o = el_id("progress");
		var progress = o.appendChild(document.createElement("p"));
		var node = progress.appendChild(document.createTextNode("uploading " + f.name));
		
		// -- Pre-flight file size check. Refuse any files larger than this.
		var max = 20; // default 20MB.
		if (f.size / 1024 / 1024 > max) {
			progress.className = "failed";
			node.nodeValue = f.name+": file too large. Please reduce the file size to under "+max+"MB.";
			continue;
		}
		
		var xhr = new XMLHttpRequest();
		// progress bar
		xhr.upload.addEventListener("progress", function(e) {
			var pc = parseInt(100 - (e.loaded / e.total * 100));
			progress.style.backgroundPosition = pc + "% 0";
		}, false);

		var n = f.name;
		// file received/failed
		xhr.onreadystatechange = function(e) {
			if (xhr.readyState == 4) {
				var response = xhr.responseText;
				var json = bl.safeJSON(response);
				if (xhr.status == 200 && json != null && json["pass"] == "1") {
					var tridEl = el_id("transactionID");
					if (tridEl != null) {
						tridEl.value = json["csrf"];
					}
					progress.className = "success";
					node.nodeValue = n+" uploaded.";
					if (callback != null) {
						callback(token, type);
					}
				}
				else {
					console.error(response);
					if (response.length == 0)
						response = "failed";
					progress.className = "failed";
					var msg = response;
					if (msg.length > 150)
						msg = msg.substr(0, 150);
					node.nodeValue = n+": "+msg;
				}
			}
		};

		// start upload
		xhr.open("POST", "MediaUpload", true);
		xhr.setRequestHeader("FILENAME", f.name);
		xhr.setRequestHeader("MIMETYPE", f.type);
		xhr.setRequestHeader("TOKEN", token);
		xhr.setRequestHeader("UTYPE", type);
		xhr.send(f);
	}
	input.value = "";
}