﻿var lib = fl.getDocumentDOM().library;
var si = lib.getSelectedItems();
if(si.length>0 )
{
	
	if(lib.getSelectedItems()[0].itemType == 'folder')
	{
		var selectedLib = lib.getSelectedItems()[0];
		var allItems = lib.items;
		var selectedFolderName = lib.getSelectedItems()[0].name;
		var itemsIndex = allItems.length;
		while(itemsIndex--)
		{
			//get only non folders items
			if (allItems[itemsIndex].itemType != 'folder' )
			{
				//get only items in this specific folder
				if(allItems[itemsIndex].name.split(selectedFolderName)[1])
				{
					//isolate the item name
					var currentEntryName = allItems[itemsIndex].name.split(selectedFolderName)[1];
					currentEntryName = currentEntryName.split("/")[1];
					allItems[itemsIndex].linkageExportForAS = true;
					allItems[itemsIndex].linkageClassName = currentEntryName;
				}
			}
		}

	} else
	{
		fl.trace('selected library item is not a folder');
	}
	//var mcName = prompt("Button_upSkin_ postfix", "");
}
// get the name of the given string without the last substring after the _
function retriveNewName(str)
{
	var ar = str.split("_");
	if (ar.length == 0)
		return str
	var newString = "";
	for (var i=0;i<ar.length-1;i++)
	{
		newString+=ar[i]+"_";
	}
	fl.trace(newString);
	return newString;
}