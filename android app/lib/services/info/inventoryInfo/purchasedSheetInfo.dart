import 'package:flutter/foundation.dart';

class PurchasedSheetInfo {
  int? purchasedSheetId;
  int? branchId;
  int? purchaserId;
  String? purchaserName;
  int? supplierId;
  String? supplierName;
  String? supplierPhone;
  int? totalPurchasePrice;
  int? discount;
  String? deliverName;
  DateTime? deliveryDate;

  PurchasedSheetInfo({
    required String purchasedSheetId,
    required String branchId,
    required String purchaserId,
    required String purchaserName,
    required String supplierId,
    required String supplierName,
    required String supplierPhone,
    required String totalPurchasePrice,
    required String discount,
    required String deliverName,
    required String deliveryDate,
  }) {
    this.purchasedSheetId = int.tryParse(purchasedSheetId) == null ? 0 : int.tryParse(purchasedSheetId);
    this.branchId = int.tryParse(branchId) == null ? 0 : int.tryParse(branchId);
    this.purchaserId =
        int.tryParse(purchaserId) == null ? 0 : int.tryParse(purchaserId);
    this.purchaserName=purchaserName;
    this.supplierId =
        int.tryParse(supplierId) == null ? 0 : int.tryParse(supplierId);
    this.supplierName=supplierName;//Must have
    this.supplierPhone=supplierPhone;//Must have
    this.totalPurchasePrice = int.tryParse(totalPurchasePrice) == null
        ? -1
        : int.tryParse(totalPurchasePrice);
    this.discount = int.tryParse(discount) == null ? -1 : int.tryParse(discount);
    if(deliverName=="null"){
      this.deliverName=null;
    }else{
      this.deliverName=deliverName;
    }
    this.deliveryDate = DateTime.tryParse(deliveryDate) == null
        ? DateTime.fromMicrosecondsSinceEpoch(0)
        : DateTime.tryParse(deliveryDate);
  }
}

class DetailPurchasedSheetInfo{
  PurchasedSheetInfo importInvoiceInfo;
  List<PurchasedItem> purchasedItems;
  DetailPurchasedSheetInfo(this.importInvoiceInfo, this.purchasedItems);
}
class PurchasedItem{
  int? purchasedSheetId;
  int? purchasedItemId;
  int? purchasePrice;
  int? quantity;
  int? itemId;
  late String name;
  String? imageUrl;
  PurchasedItem({required String purchasedSheetId,required String purchasedItemId,required String purchasePrice,required String quantity,required String itemId,required String name, required String imageUrl}){
    this.purchasedSheetId=int.tryParse(purchasedSheetId)==null?-1:int.tryParse(purchasedSheetId);
    this.purchasedItemId=int.tryParse(purchasedItemId)==null?-1:int.tryParse(purchasedItemId);
    this.purchasePrice=int.tryParse(purchasePrice)==null?-1:int.tryParse(purchasePrice);
    this.quantity=int.tryParse(quantity)==null?-1:int.tryParse(quantity);
    this.itemId=int.tryParse(itemId)==null?-1:int.tryParse(itemId);
    this.name=name;
    this.imageUrl=imageUrl;
  }
}