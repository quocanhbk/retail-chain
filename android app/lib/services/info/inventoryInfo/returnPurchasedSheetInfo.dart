class ReturnPurchasedSheetInfo{
  late int returnPurchasedSheetId;
  late int purchasedSheetId;
  late int returnerId;
  late String returnerName;
  late int? supplierId;
  late String? supplierName;
  late int totalReturnMoney;
  late DateTime createdDateTime;

  ReturnPurchasedSheetInfo({
      required String returnPurchasedSheetId,
      required String  purchasedSheetId,
      required String returnerId,
      required this.returnerName,
    required String supplierId,
    required String supplierName,
      required String totalReturnMoney,
      required String createdDateTime}){
    this.returnPurchasedSheetId=int.tryParse(returnPurchasedSheetId)??0;
    this.purchasedSheetId=int.tryParse(purchasedSheetId)??0;
    this.returnerId=int.tryParse(returnerId)??0;
    this.supplierId=int.tryParse(supplierId)??null;
    this.supplierName=supplierName=="null"?null:supplierName;
    this.totalReturnMoney=int.tryParse(totalReturnMoney)??0;
    this.createdDateTime=DateTime.tryParse(createdDateTime)??DateTime.fromMicrosecondsSinceEpoch(0);
  }
}
class DetailReturnPurchasedSheetInfo{
  ReturnPurchasedSheetInfo returnPurchasedSheet;
  List<ReturnItemInfo> returnItems;

  DetailReturnPurchasedSheetInfo({required this.returnPurchasedSheet, required this.returnItems});
}
class ReturnItemInfo{
  late int returnPurchasedSheetId;
  late int returnPurchasedItemID;
  late int itemId;
  late String name;
  String? imageUrl;
  late int oldPurchasedPrice;
  late int oldQuantity;

  ReturnItemInfo({
      required String returnPurchasedSheetId,
      required String returnPurchasedItemID,
      required String itemId,
      required this.name,
      required this.imageUrl,
      required String oldPurchasedPrice,
      required String oldQuantity}){
    this.returnPurchasedSheetId=int.tryParse(returnPurchasedSheetId)??0;
    this.returnPurchasedItemID=int.tryParse(returnPurchasedItemID)??0;
    this.itemId=int.tryParse(itemId)??0;
    this.oldPurchasedPrice=int.tryParse(oldPurchasedPrice)??0;
    this.oldQuantity=int.tryParse(oldQuantity)??0;
  }
}