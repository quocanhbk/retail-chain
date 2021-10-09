// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'categoryInfo.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

CategoryInfo _$CategoryInfoFromJson(Map<String, dynamic> json) {
  return CategoryInfo(
    id: json['id'].toString(),
    name: json['name'].toString(),
    storeId: json['storeId'].toString(),
    createdAt: json['createdAt'].toString(),
    updatedAt: json['updatedAt'].toString(),
    pointRatio: json['pointRatio'].toString(),
    deleted: json['deleted'].toString(),
  );
}

Map<String, dynamic> _$CategoryInfoToJson(CategoryInfo instance) =>
    <String, dynamic>{
      'name': instance.name,
      'pointRatio': instance.pointRatio,
      'updatedAt': instance.updatedAt?.toIso8601String(),
      'createdAt': instance.createdAt?.toIso8601String(),
      'deleted': instance.deleted,
      'storeId': instance.storeId,
      'id': instance.id,
    };
