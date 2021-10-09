import 'package:intl/intl.dart';

class ShiftInfo{
  int? shiftId;
  String? name;
  DateTime? startTime;
  DateTime? endTime;
  bool? monday;
  bool? tuesday;
  bool? wednesday;
  bool? thursday;
  bool? friday;
  bool? saturday;
  bool? sunday;
  DateTime? startDate;
  DateTime? endDate;

  ShiftInfo({
    required String shiftId,
    String? name,
    required String startTime,
    required String endTime,
    String? monday,
    String? tuesday,
    String? wednesday,
    String? thursday,
    String? friday,
    String? saturday,
    String? sunday,
    required String startDate,
    String? endDate
  }){
    this.shiftId=int.tryParse(shiftId)!=null?int.tryParse(shiftId):-1;
    this.name=name;//Must have
    try{
      this.startTime=DateFormat("HH:mm:ss").parse(startTime);
    }on FormatException {
      this.startTime=DateTime.fromMicrosecondsSinceEpoch(0);
    }
    try{
      this.endTime=DateFormat("HH:mm:ss").parse(endTime);
    }on FormatException {
      this.endTime=DateTime.fromMicrosecondsSinceEpoch(0);
    }
    this.monday=monday=="1";
    this.tuesday=tuesday=="1";
    this.wednesday=wednesday=="1";
    this.thursday=thursday=="1";
    this.friday=friday=="1";
    this.saturday=saturday=="1";
    this.sunday=sunday=="1";
    this.startDate=DateTime.tryParse(startDate)==null?DateTime.fromMicrosecondsSinceEpoch(0):DateTime.tryParse(startDate);
    if(endDate=="null"){
      this.endDate=null;
    }else{
      this.endDate=DateTime.tryParse(endDate!)==null?DateTime.fromMicrosecondsSinceEpoch(0):DateTime.tryParse(endDate);
    }
  }
}