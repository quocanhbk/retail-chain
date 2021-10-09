import 'package:flutter/foundation.dart';
import 'package:intl/intl.dart';
import 'package:json_annotation/json_annotation.dart';

part 'itemInfo.g.dart';

@JsonSerializable()
class ItemInfo {
  ItemInfo(
      {required String itemId,
      required String categoryId,
      required String categoryName,
      required String? itemName,
      required String barCode,
      required String imageUrl,
      required String sellPrice,
      required String priceId,
      required String quantity,
        required String purchasePrice,
      required String createdDate,
      required String pointRatio}) {
    this.itemId = int.tryParse(itemId)??0;
    this.categoryId =
        int.tryParse(categoryId)??0;
    this.categoryName = categoryName;//Must have
    this.itemName = itemName;//Must have
    if(barCode=="null"){
      this.barCode=null;
    }else{
      this.barCode = barCode;
    }
    if(imageUrl=="null"){
      this.imageUrl=null;
    }else{
      this.imageUrl = imageUrl;
    }
    this.sellPrice = int.tryParse(sellPrice)??0;
    this.priceId = int.tryParse(priceId)??0;
    this.quantity = int.tryParse(quantity)??0;
    this.purchasePrice=int.tryParse(purchasePrice)??0;
    this.createdDate=DateTime.tryParse(createdDate)??DateTime.fromMicrosecondsSinceEpoch(0);
    this.pointRatio=double.tryParse(pointRatio)??0;
  }

  late int itemId;
  late int categoryId;
  late String categoryName;
  String? itemName;
  String? barCode;
  String? imageUrl;
  late int sellPrice;
  late int priceId;
  late int quantity;
  late int purchasePrice;
  late DateTime createdDate;
  late double pointRatio;

  @override
  String toString() {
    return "Item id=" +
        itemId.toString() +
        ",categoryId=" +
        categoryId.toString() +
        ",quantity=" +
        quantity.toString() +
        ",priceId=" +
        priceId.toString() +
        ",price=" +
        sellPrice.toString() +
        ",imageUrl=" +
        imageUrl.toString() +
        ",barCode=" +
        barCode.toString() +
        ",name=" +
        itemName.toString();
  }
  factory ItemInfo.fromJson(Map<String, dynamic> json) => _$ItemInfoFromJson(json);
  Map<String, dynamic> toJson() => _$ItemInfoToJson(this);

}
