import 'itemInfo.dart';

class QuantityHistory{
  ItemInfo? item;
  List<ItemQuantityChange> quantityHistory;

  QuantityHistory(this.item, this.quantityHistory);
}
class ItemQuantityChange{
  late String quantityChange;
  late int itemId;
  late int oldQuantity;
  late int newQuantity;
  late int checkerId;
  late String checkerName;
  late String reason;
  late DateTime createdDatetime;

  ItemQuantityChange({required this.quantityChange, required String itemId,required String oldQuantity, required String newQuantity,required this.reason,
      required String checkerId, required this.checkerName, required String createdDatetime}){
    this.itemId=int.tryParse(itemId)??0;
    this.oldQuantity=int.tryParse(oldQuantity)??0;
    this.newQuantity=int.tryParse(newQuantity)??0;
    this.checkerId=int.tryParse(checkerId)??0;
    this.createdDatetime=DateTime.tryParse(createdDatetime)??DateTime.now();
  }
}
