import 'package:flutter/cupertino.dart';
import 'package:json_annotation/json_annotation.dart';

part 'categoryInfo.g.dart';

@JsonSerializable()

class CategoryInfo{
  int? _id;
  late String name;
  int? _storeId;
  bool? _deleted;
  DateTime? _createdAt;
  DateTime? _updatedAt;
  late double pointRatio;
  CategoryInfo({required String id, required String name, required String storeId, required String deleted,
    required String createdAt, required String updatedAt,required String pointRatio}){
    this._id=int.tryParse(id)==null?0:int.tryParse(id);
    this.name=name;//Must have
    this._storeId=int.tryParse(storeId)==null?0:int.tryParse(storeId);
    this._deleted= int.tryParse(deleted)==null?false:(int.tryParse(deleted)!=0);
    this._createdAt=DateTime.tryParse(createdAt)==null?DateTime.fromMicrosecondsSinceEpoch(0):DateTime.tryParse(createdAt);
    this._updatedAt=DateTime.tryParse(updatedAt)==null?DateTime.fromMicrosecondsSinceEpoch(0):DateTime.tryParse(updatedAt);
    this.pointRatio=double.tryParse(pointRatio)??0;
  }

  DateTime? get updatedAt => _updatedAt;

  DateTime? get createdAt => _createdAt;

  bool? get deleted => _deleted;

  int? get storeId => _storeId;

  int? get id => _id;

  factory CategoryInfo.fromJson(Map<String, dynamic> json) => _$CategoryInfoFromJson(json);
  Map<String, dynamic> toJson() => _$CategoryInfoToJson(this);
}