import 'package:flutter/foundation.dart';

class Store{
  int? _id;
  String? _name;
  String? _chainId;
  String? _address;
  DateTime? _createdTime;
  DateTime? _updatedTime;

  Store({required String id,required String name,required String chainId,required String address,String? createdTime,String? updatedTime}){
    this._id=int.parse(id);
    this._name=name;//Must have
    this._chainId=chainId;
    this._address=address;//Must have
    if(createdTime!="null"){
      this._createdTime=DateTime.parse(createdTime!);
    }
    if(updatedTime!="null"){
      this._updatedTime=DateTime.parse(updatedTime!);
    }
  }

  DateTime? get updatedTime => _updatedTime;

  DateTime? get createdTime => _createdTime;

  String? get address => _address;

  String? get chainId => _chainId;

  String? get name => _name;

  int? get id => _id;
}