import 'package:flutter/foundation.dart';

class ScheduleInfo {
  int? shiftId;
  int? scheduleId;
  DateTime? scheduleStartDate;
  DateTime? scheduleEndDate;
  int? userId;
  String? name;
  String? status;

  ScheduleInfo(
      {required String shiftId,
      required String scheduleId,
      required String scheduleStartDate,
      required String scheduleEndDate,
      required String userId,
      required String name,
      required String status}){
    this.shiftId=int.tryParse(shiftId)==null?-1:int.tryParse(shiftId);
    this.scheduleId=int.tryParse(scheduleId)==null?-1:int.tryParse(scheduleId);
    this.scheduleStartDate=DateTime.tryParse(scheduleStartDate)!=null?DateTime.tryParse(scheduleStartDate):DateTime.fromMicrosecondsSinceEpoch(0);
    this.scheduleEndDate=DateTime.tryParse(scheduleEndDate);
    this.userId=int.tryParse(userId)==null?-1:int.tryParse(userId);
    this.name=name;//Must have
    if(status=="null"){
      this.status=null;
    }else{
      this.status=status;
    }
  }
}
