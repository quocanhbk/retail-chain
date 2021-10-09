import 'dart:io';

import 'package:flutter/foundation.dart';
import 'package:json_annotation/json_annotation.dart';
part 'userInfo.g.dart';
@JsonSerializable()
class UserInfo{
  int? userId;
  late String name;
  String? email;
  late String username;
  String? phone;
  String? gender;
  DateTime? dateOfBirth;
  int? storeId;
  String? storeName;
  int? storeOwnerId;
  int? branchId;
  late String branchName;
  late String branchAddress;
  String? token;
  late List<String> roles;
  late String avatarUrl;
  late String avatarFile;
  late bool stayLoggedIn;
  UserInfo(
  {required String userId,
    required String name,
    required String email,
    required String username,
    required String gender,
    required String phone,
    required String dateOfBirth,
    required String storeId,
    required String storeName,
    required String storeOwnerId,
    required String branchId,
    required String branchName,
    required String branchAddress,
    required List<dynamic> roles,
    required String? token,
    required this.avatarUrl,
  required this.avatarFile,
   bool? stayLoggedIn}){
    this.username=username;//Must have
    this.userId = int.tryParse(userId)==null?0:int.tryParse(userId);
    this.name=name;//Must have
    if(email=="null"){
      this.email=null;
    }else{
      this.email=email;
    }
    if(gender=="null"){
      this.gender=null;
    }else{
      this.gender=gender;
    }
    if(phone=="null"){
      this.phone=null;
    }else{
      this.phone=phone;
    }
    this.dateOfBirth=DateTime.tryParse(dateOfBirth)==null?DateTime.fromMicrosecondsSinceEpoch(0):DateTime.tryParse(dateOfBirth);
    this.storeId=int.tryParse(storeId)==null?0:int.tryParse(storeId);
    this.storeName=storeName;//Must have
    this.storeOwnerId=int.tryParse(storeOwnerId)==null?0:int.tryParse(storeOwnerId);
    this.branchId=int.tryParse(branchId)==null?0:int.tryParse(branchId);
    this.branchName=branchName;//Must have
    this.branchAddress=branchAddress;//Must have
    List<String> convertRole = [];
    roles.forEach((element) {
      convertRole.add(element.toString());
    });
    this.roles=convertRole;
    this.token=token;
    this.stayLoggedIn=stayLoggedIn=="true";
  }
  factory UserInfo.fromJson(Map<String, dynamic> json) => _$UserInfoFromJson(json);
  Map<String, dynamic> toJson() => _$UserInfoToJson(this);

  @override
  String toString() {
    return 'UserInfo{userId: $userId, name: $name, email: $email, username: $username, phone: $phone, gender: $gender, dateOfBirth: $dateOfBirth, storeId: $storeId, storeName: $storeName, storeOwnerId: $storeOwnerId, branchId: $branchId, branchName: $branchName, branchAddress: $branchAddress, token: $token, roles: $roles, avatarUrl: $avatarUrl, avatarFile: $avatarFile, stayLoggedIn: $stayLoggedIn}';
  }
}
