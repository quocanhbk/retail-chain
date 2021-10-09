import 'package:flutter/foundation.dart';

class SupplierInfo {
  int? _id;
  int? _branchId;
  String? _name;
  String? _phoneNumber;
  String? _email;
  String? _address;
  bool? deleted;
  SupplierInfo(
      {required String id,
      required String branchId,
      required String name,
      required String phoneNumber,
      String? email,
      String? address,
      String? createdAt,
      String? updatedAt,
      required String deleted}) {
    this._id = int.tryParse(id) == null ? 0 : int.tryParse(id);
    this._branchId = int.tryParse(branchId) == null ? 0 : int.tryParse(branchId);
    this._name = name;//Must have
    this._phoneNumber = phoneNumber;//Must have
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
    this.deleted =
        int.tryParse(deleted) == null ? false : (int.tryParse(deleted) == 1);
  }

  String? get address => _address;

  String? get email => _email;

  String? get phoneNumber => _phoneNumber;

  String? get name => _name;

  int? get id => _id;

  set email(String? value) {
    if (value != null) {
      if (RegExp(
              r"^[a-zA-Z0-9.a-zA-Z0-9.!#$%&'*+-/=?^_`{|}~]+@[a-zA-Z0-9]+\.[a-zA-Z]+")
          .hasMatch(value)) {
        _email = value;
      }
    }else{
      this._email=null;
    }
  }

  set address(String? value) {
      _address = value;
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

  int? get storeId => _branchId;
}
