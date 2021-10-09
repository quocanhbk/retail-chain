import 'package:flutter/foundation.dart';

class EmployeeInfo {
  late int branchId;
  late int userId;
  late String userName;
  late String name;
  String? email;
  String? phone;
  DateTime? dateOfBirth;
  String? gender;
  String? status;
  List<String>? roles;
  EmployeeInfo(
      {required String branchId,
      required String userId,
        required String userName,
      required String name,
      required String email,
      required String phone,
      String? dateOfBirth,
      required String gender,
      required String status,
      List<String>? roles}) {
    this.branchId=int.tryParse(branchId)==null?-1:int.parse(branchId);
    this.userId=int.tryParse(userId)==null?-1:int.parse(userId);
    this.name=name;
    this.userName=userName;
    if(email=="null"){
      this.email=null;
    }else{
      this.email=email;
    }
    if(phone=="null"){
      this.phone=null;
    }else{
      this.phone=phone;
    }
    this.gender=gender=="null"?null:gender;
    this.dateOfBirth=dateOfBirth!=null?DateTime.tryParse(dateOfBirth):null;
    this.roles=roles;
    this.status=status=="null"?null:status;
  }
}
