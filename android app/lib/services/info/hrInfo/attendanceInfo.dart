import 'package:flutter/foundation.dart';

class AttendanceInfo {
  int? shiftId;
  int? scheduleId;
  int? userId;
  String? name;
  DateTime? date;

  AttendanceInfo(
      {required String shiftId,
      required String scheduleId,
      required String userId,
      required String name,
      required String date}){
    this.shiftId=int.tryParse(shiftId)!=null?int.tryParse(shiftId):-1;
    this.scheduleId=int.tryParse(scheduleId)!=null?int.tryParse(scheduleId):-1;
    this.userId=int.tryParse(userId)!=null?int.tryParse(userId):-1;
    if(name=="null"){
      this.name=null;
    }else{
      this.name = name;
    }
    this.date=DateTime.tryParse(date)!=null?DateTime.tryParse(date):null;
  }
}
