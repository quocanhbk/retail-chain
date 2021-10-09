import 'package:flutter/foundation.dart';

class DefaultItemInfo {
  DefaultItemInfo(
      {required String id,
      required String categoryId,
        required String categoryName,
      required String name,
      required String barCode,
      required String imageUrl,
      required String createdDate,
      required String deleted}) {
    _itemId = int.tryParse(id) == null ? 0 : int.tryParse(id);
    _categoryId =
        int.tryParse(categoryId) == null ? 0 : int.tryParse(categoryId);
    _categoryName=categoryName;
    if(name=="null"){
      this._itemName=null;
    }else{
      _itemName = name;
    }
    if(barCode=="null"){
      this._barCode=null;
    }else{
      this._barCode = barCode;
    }
    if(imageUrl=="null"){
      this._imageUrl=null;
    }else{
      _imageUrl = imageUrl;
    }
    _createdDate=DateTime.tryParse(createdDate)==null?DateTime.fromMicrosecondsSinceEpoch(0):DateTime.tryParse(createdDate);
  }

  int? _itemId;
  int? _categoryId;
  String? _categoryName;
  String? _itemName;
  String? _barCode;
  String? _imageUrl;
  DateTime? _createdDate;
  bool? _deleted;

  int? get categoryId => _categoryId;

  int? get itemId => _itemId;

  String? get imageUrl => _imageUrl;

  String? get barCode => _barCode;

  String? get itemName => _itemName;

  DateTime? get createdDate => _createdDate;

  String? get categoryName => _categoryName;

  @override
  String toString() {
    return "Item id=" +
        itemId.toString() +
        ",categoryId=" +
        categoryId.toString() +
        ",categoryName=" +
        _categoryName! +
        ",imageUrl=" +
        imageUrl! +
        ",barCode=" +
        barCode! +
        ",name=" +
        itemName!;
  }

  // String get unitName => _unitName;
}
