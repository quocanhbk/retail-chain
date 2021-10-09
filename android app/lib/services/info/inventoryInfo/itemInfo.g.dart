// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'itemInfo.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

ItemInfo _$ItemInfoFromJson(Map<String, dynamic> json) {
  return ItemInfo(
    itemId: json['itemId'].toString(),
    categoryId: json['categoryId'].toString(),
    categoryName: json['categoryName'].toString(),
    itemName: json['itemName'].toString(),
    barCode: json['barCode'].toString(),
    imageUrl: json['imageUrl'].toString(),
    sellPrice: json['sellPrice'].toString(),
    priceId: json['priceId'].toString(),
    quantity: json['quantity'].toString(),
    purchasePrice: json["purchasePrice"].toString(),
    createdDate: json['createdDate'].toString(),
    pointRatio: json['pointRatio'].toString(),
  );
}

Map<String, dynamic> _$ItemInfoToJson(ItemInfo instance) => <String, dynamic>{
      'itemId': instance.itemId,
      'categoryId': instance.categoryId,
      'categoryName': instance.categoryName,
      'itemName': instance.itemName,
      'barCode': instance.barCode,
      'imageUrl': instance.imageUrl,
      'sellPrice': instance.sellPrice,
      'priceId': instance.priceId,
      'quantity': instance.quantity,
      'purchasePrice': instance.purchasePrice,
      'createdDate': instance.createdDate.toIso8601String(),
      'pointRatio': instance.pointRatio,
    };
