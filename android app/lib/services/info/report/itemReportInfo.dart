class ItemReportInfo{
  late List<Item> top3TotalSellPriceItems;
  late List<Item> top3SoldQuantityItems;
  late int totalSellPrice;
  late int totalSellQuantity;

  ItemReportInfo({required this.top3TotalSellPriceItems, required this.top3SoldQuantityItems,
      required String totalSellPrice, required String totalSellQuantity}){
    this.totalSellPrice=int.tryParse(totalSellPrice)??0;
    this.totalSellQuantity=int.tryParse(totalSellQuantity)??0;
  }
}
class Item{
  late int id;
  late String name;
  late String imageUrl;
  late int totalSellPrice;
  late int totalQuantity;

  Item({required String id, required this.name,required  this.imageUrl,required String totalSellPrice,
      required String totalQuantity}){
    this.id=int.tryParse(id)??-1;
    this.totalSellPrice=int.tryParse(totalSellPrice)??0;
    this.totalQuantity=int.tryParse(totalQuantity)??0;
  }
}