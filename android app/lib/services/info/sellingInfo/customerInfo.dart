import 'package:flutter/foundation.dart';
import 'package:json_annotation/json_annotation.dart';

part 'customerInfo.g.dart';

@JsonSerializable()
class CustomerInfo {
  int? _id;
  int? _branchId;
  String? _name;
  String? _phoneNumber;
  String? _address;
  String? _gender;
  String? _email;
  int? customerPoint;
  DateTime? _dateOfBirth;
  String? customerCode;
  late DateTime? createdDate;
  bool? deleted;

  CustomerInfo(
      {required String id,
      required String storeId,
      required String name,
      required String phoneNumber,
      required String customerPoint,
      required String email,
      required String address,
      required String gender,
      required String dateOfBirth,
      required String customerCode,
      required String createdDate,
      required String deleted}) {
    this._id = int.tryParse(id)??0;
    this._branchId = int.tryParse(storeId)??0;
    if(name=="null"){
      this._name=null;
    }else{
      this._name = name;
    }
    this._phoneNumber=phoneNumber;//Must have
    if(email=="null"){
      this._email=null;
    }else{
      this._email = email;
    }
    if(address=="null"){
      this._address=null;
    }else{
      this._address = address;
    }
    if(gender=="null"){
      this._gender=null;
    }else{
      this._gender = gender;
    }
    this.customerPoint =
        int.tryParse(customerPoint)??0;
    this.createdDate = DateTime.tryParse(createdDate)??null;
    this._dateOfBirth=DateTime.tryParse(dateOfBirth)??null;
    this.deleted =
        int.tryParse(deleted) == null ? false : (int.tryParse(deleted) == 1);
  }

  String? get phoneNumber => _phoneNumber;

  String? get name => _name;

  int? get id => _id;

  String? get email => _email;

  String? get gender => _gender;

  String? get address => _address;

  int? get storeId => _branchId;

  int? get branchId => _branchId;

  set email(String? value) {
    if (value != null || value != "") {
      if (RegExp(
              r"^[a-zA-Z0-9.a-zA-Z0-9.!#$%&'*+-/=?^_`{|}~]+@[a-zA-Z0-9]+\.[a-zA-Z]+")
          .hasMatch(value!)) {
        _email = value;
      }
    }
  }

  set gender(String? value) {
    if (value != null || value != "") {
      if (value == "male" || gender == "female") {
        _gender = value;
      }
    }
  }

  set address(String? value) {
    if (value != "" || value != null) {
      _address = value;
    }
  }

  set phoneNumber(String? value) {
    if (value != null || value != "") {
      this._phoneNumber = value;
    }
  }

  set name(String? value) {
    if (value != null || value != "") {
      this._name = name;
    }
  }


  set dateOfBirth(DateTime? value) {
    if(value!=null){
      _dateOfBirth = value;
    }
  }


  DateTime? get dateOfBirth => _dateOfBirth;
  factory CustomerInfo.fromJson(Map<String, dynamic> json) => _$CustomerInfoFromJson(json);
  Map<String, dynamic> toJson() => _$CustomerInfoToJson(this);
}
