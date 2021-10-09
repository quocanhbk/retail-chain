// GENERATED CODE - DO NOT MODIFY BY HAND

part of '../sellingInfo/customerInfo.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

CustomerInfo _$CustomerInfoFromJson(Map<String, dynamic> json) {
  return CustomerInfo(
    id: json['id'].toString(),
    storeId: json['storeId'].toString(),
    name: json['name'].toString(),
    phoneNumber: json['phoneNumber'].toString(),
    customerPoint: json['customerPoint'].toString(),
    email: json['email'].toString(),
    address: json['address'].toString(),
    gender: json['gender'].toString(),
    dateOfBirth: json['dateOfBirth'].toString(),
    customerCode: json['customerCode'].toString(),
    createdDate: json['createdDate'].toString(),
    deleted: json['deleted'].toString(),
  );
}

Map<String, dynamic> _$CustomerInfoToJson(CustomerInfo instance) =>
    <String, dynamic>{
      'customerPoint': instance.customerPoint,
      'customerCode': instance.customerCode,
      'createdDate': instance.createdDate?.toIso8601String(),
      'deleted': instance.deleted,
      'phoneNumber': instance.phoneNumber,
      'name': instance.name,
      'id': instance.id,
      'email': instance.email,
      'gender': instance.gender,
      'address': instance.address,
      'storeId': instance.storeId,
      'dateOfBirth': instance.dateOfBirth?.toIso8601String(),
    };
