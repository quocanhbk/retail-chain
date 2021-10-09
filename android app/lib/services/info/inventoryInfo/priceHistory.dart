import 'itemInfo.dart';

class PriceHistory{
  ItemInfo item;
  List<ItemPrice> prices;

  PriceHistory(this.item,this.prices);
}

class ItemPrice{
  late int itemId;
  late String name;
  late int sellPrice;
  late String changedBy;
  late DateTime fromDate;
  DateTime? toDate;

  ItemPrice({required String itemId, required String name, required String sellPrice, required String changedBy,
      required String fromDate, required String toDate}){
    this.itemId=int.tryParse(itemId)??0;
    this.name=name;
    this.sellPrice=int.tryParse(sellPrice)??0;
    this.changedBy=changedBy=="null"?"Không có":changedBy;
    this.fromDate=DateTime.tryParse(fromDate)??DateTime.now();
    this.toDate=DateTime.tryParse(toDate);
  }

}