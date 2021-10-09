// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'userInfo.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

UserInfo _$UserInfoFromJson(Map<String, dynamic> json) {
  return UserInfo(
    userId: json['userId'].toString(),
    name: json['name'].toString(),
    email: json['email'].toString(),
    username: json['username'].toString(),
    gender: json['gender'].toString(),
    phone: json['phone'].toString(),
    dateOfBirth: json['dateOfBirth'].toString(),
    storeId: json['storeId'].toString(),
    storeName: json['storeName'].toString(),
    storeOwnerId: json['storeOwnerId'].toString(),
    branchId: json['branchId'].toString(),
    branchName: json['branchName'].toString(),
    branchAddress: json['branchAddress'].toString(),
    roles: json['roles'] as List<dynamic>,
    token: json['token'].toString(),
    avatarUrl: json['avatarUrl'].toString(),
    avatarFile: json['avatarFile'].toString(),
    stayLoggedIn: json['stayLoggedIn']
  );
}

Map<String, dynamic> _$UserInfoToJson(UserInfo instance) => <String, dynamic>{
      'userId': instance.userId,
      'name': instance.name,
      'email': instance.email,
      'username': instance.username,
      'phone': instance.phone,
      'gender': instance.gender,
      'dateOfBirth': instance.dateOfBirth?.toIso8601String(),
      'storeId': instance.storeId,
      'storeName': instance.storeName,
      'storeOwnerId': instance.storeOwnerId,
      'branchId': instance.branchId,
      'branchName': instance.branchName,
      'branchAddress': instance.branchAddress,
      'token': instance.token,
      'roles': instance.roles,
  'avatarFile':instance.avatarFile,
  'avatarUrl':instance.avatarUrl,
  "stayLoggedIn":instance.stayLoggedIn
    };
