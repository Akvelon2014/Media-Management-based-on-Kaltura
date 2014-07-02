/**
* Unless required by applicable law or agreed to in writing, software
* distributed under the License is distributed on an "AS IS" BASIS,
* WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
* See the License for the specific language governing permissions and
* limitations under the License.
*
* Modified by Akvelon Inc.
* 2014-06-30
* http://www.akvelon.com/contact-us
*/

package com.kaltura.edw.control.commands.relatedFiles
{
	import com.kaltura.commands.MultiRequest;
	import com.kaltura.commands.attachmentAsset.AttachmentAssetAdd;
	import com.kaltura.commands.attachmentAsset.AttachmentAssetDelete;
	import com.kaltura.commands.attachmentAsset.AttachmentAssetSetContent;
	import com.kaltura.commands.attachmentAsset.AttachmentAssetUpdate;
	import com.kaltura.edw.control.commands.KedCommand;
	import com.kaltura.edw.control.events.RelatedFileEvent;
	import com.kaltura.edw.model.datapacks.EntryDataPack;
	import com.kaltura.edw.vo.RelatedFileVO;
	import com.kaltura.events.KalturaEvent;
	import com.kaltura.kmvc.control.KMvCEvent;
	import com.kaltura.vo.KalturaUploadedFileTokenResource;
	
	public class SaveRelatedFilesCommand extends KedCommand
	{
		override public function execute(event:KMvCEvent):void {
			var evt:RelatedFileEvent = event as RelatedFileEvent;

			var mr:MultiRequest = new MultiRequest();
			mr.queued = false;
			mr.useTimeout = false;
			var requestIndex:int = 1;
			//add assets
			if (evt.relatedToAdd) {
				for each (var relatedFile:RelatedFileVO in evt.relatedToAdd) {
					//add asset
					var addFile:AttachmentAssetAdd = new AttachmentAssetAdd((_model.getDataPack(EntryDataPack) as EntryDataPack).selectedEntry.id, relatedFile.file);
					mr.addAction(addFile);
					requestIndex++;
					//set its content
					var resource:KalturaUploadedFileTokenResource = new KalturaUploadedFileTokenResource();
					resource.token = relatedFile.uploadTokenId;
					var addContent:AttachmentAssetSetContent = new AttachmentAssetSetContent('0', resource);
					mr.mapMultiRequestParam(requestIndex-1, "id", requestIndex, "id");
					mr.addAction(addContent);
					requestIndex++;	
				}
			}
			//update assets
			if (evt.relatedToUpdate) {
				for each (var updateRelated:RelatedFileVO in evt.relatedToUpdate) {
					updateRelated.file.setUpdatedFieldsOnly(true);
					var updateAsset:AttachmentAssetUpdate = new AttachmentAssetUpdate(updateRelated.file.id, updateRelated.file);
					mr.addAction(updateAsset);
					requestIndex++;
				}
			}
			if (evt.relatedToDelete) {
				for each (var deleteRelated:RelatedFileVO in evt.relatedToDelete) {
					var deleteAsset:AttachmentAssetDelete = new AttachmentAssetDelete(deleteRelated.file.id);
					mr.addAction(deleteAsset);
					requestIndex++;
				}
			}
			
			if (requestIndex > 1) {
				_model.increaseLoadCounter();
				mr.addEventListener(KalturaEvent.COMPLETE, result);
				mr.addEventListener(KalturaEvent.FAILED, fault);
				
				_client.post(mr);
			}
		}
		
		override public function result(data:Object):void {
			super.result(data);
			_model.decreaseLoadCounter();
		}
	}
}