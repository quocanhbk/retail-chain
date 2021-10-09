import 'package:bkrm/services/services.dart';
import 'package:flutter/material.dart';
import 'package:flutter_datetime_picker/flutter_datetime_picker.dart';
import 'package:intl/intl.dart';
import 'package:weekday_selector/weekday_selector.dart';

class AddNewShiftPage extends StatefulWidget {
  @override
  _AddNewShiftPageState createState() => _AddNewShiftPageState();
}

class _AddNewShiftPageState extends State<AddNewShiftPage> {
  final _formKey = GlobalKey<FormState>();

  bool needRefresh = false;

  List<bool> weekdays = List.filled(7, false);

  DateTime? startTime;
  DateTime? endTime;
  DateTime? startDate;
  DateTime? endDate;

  bool nameValid = false;
  bool startTimeValid = false;
  bool endTimeValid = false;
  bool startDateValid = false;

  TextEditingController nameController = TextEditingController();
  TextEditingController startTimeController = TextEditingController();
  TextEditingController endTimeController = TextEditingController();
  TextEditingController startDateController = TextEditingController();
  TextEditingController endDateController = TextEditingController();

  TextStyle titleStyle = TextStyle(fontSize: 16, fontWeight: FontWeight.bold);

  clearPage() {
    nameController.text = "";
    startTimeController.text = "";
    endTimeController.text = "";
    startDateController.text = "";
    endDateController.text = "";
    nameValid = false;
    startTimeValid = false;
    endTimeValid = false;
    startDateValid = false;
    startTime = null;
    endTime = null;
    startDate = null;
    endDate = null;
    weekdays=List.filled(7, false);
    setState(() {});
  }

  bool checkValidBeforeSubmit() {
    setState(() {});
    if (!(weekdays[0] ||
        weekdays[1] ||
        weekdays[2] ||
        weekdays[3] ||
        weekdays[4] ||
        weekdays[5] ||
        weekdays[6])) {
      return false;
    }
    _formKey.currentState!.validate();

    if (!(nameValid && startTimeValid && endTimeValid && startDateValid)) {
      return false;
    }
    return true;
  }

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: () {
        FocusScope.of(context).requestFocus(FocusNode());
      },
      child: Scaffold(
        appBar: AppBar(
          title: Text("Tạo ca mới"),
        ),
        body: WillPopScope(
          onWillPop: ()async{
            Navigator.pop(context,needRefresh);
            return needRefresh;
          },
          child: SingleChildScrollView(
            child: Form(
              key: _formKey,
              child: Container(
                padding: EdgeInsets.all(8.0),
                child: Column(
                  children: [
                    SizedBox(
                      height: 30,
                    ),
                    Row(
                      children: [
                        Expanded(
                            flex: 1,
                            child: Text(
                              "Tên ca:",
                              style: titleStyle,
                            )),
                        Expanded(
                            flex: 2,
                            child: TextFormField(
                              maxLength: 50,
                              controller: nameController,
                              autovalidateMode: AutovalidateMode.always,
                              validator: (name) {
                                if (name == null || name == "") {
                                  nameValid = false;
                                  return "* Bắt buộc";
                                }
                                nameValid = true;
                                return null;
                              },
                            )),
                      ],
                    ),
                    SizedBox(
                      height: 10,
                    ),
                    Row(
                      children: [
                        Expanded(
                            flex: 2,
                            child: Text(
                              "Thời gian bắt đầu:",
                              style: titleStyle,
                            )),
                        Expanded(
                            flex: 4,
                            child: InkWell(
                              onTap: () {
                                DatePicker.showTimePicker(context,
                                    showSecondsColumn: false,
                                    locale: LocaleType.vi,
                                    onConfirm: (DateTime value) {
                                  value = value
                                      .subtract(Duration(seconds: value.second));
                                  startTime = value;
                                  startTimeController.text =
                                      DateFormat("HH:mm:ss").format(value);
                                  setState(() {});
                                });
                              },
                              child: IgnorePointer(
                                child: TextFormField(
                                  textAlign: TextAlign.center,
                                  controller: startTimeController,
                                  autovalidateMode: AutovalidateMode.always,
                                  validator: (time) {
                                    if (time == null || time == "") {
                                      startTimeValid = false;
                                      return "* Bắt buộc";
                                    }
                                    startTimeValid = true;
                                    return null;
                                  },
                                ),
                              ),
                            )),
                        Expanded(
                            flex: 1,
                            child: IconButton(
                              icon: Icon(Icons.close),
                              onPressed: () {
                                startTime = null;
                                startTimeController.text = "";
                              },
                            )),
                      ],
                    ),
                    SizedBox(
                      height: 10,
                    ),
                    Row(
                      children: [
                        Expanded(
                            flex: 2,
                            child: Text(
                              "Thời gian kết thúc:",
                              style: titleStyle,
                            )),
                        Expanded(
                            flex: 4,
                            child: InkWell(
                              onTap: () {
                                DatePicker.showTimePicker(context,
                                    showSecondsColumn: false,
                                    locale: LocaleType.vi,
                                    onConfirm: (DateTime value) {
                                  value = value
                                      .subtract(Duration(seconds: value.second));
                                  endTime = value;
                                  endTimeController.text =
                                      DateFormat("HH:mm:ss").format(value);
                                  setState(() {});
                                });
                              },
                              child: IgnorePointer(
                                child: TextFormField(
                                  textAlign: TextAlign.center,
                                  controller: endTimeController,
                                  autovalidateMode: AutovalidateMode.always,
                                  validator: (time) {
                                    if (time == null || time == "") {
                                      endTimeValid = false;
                                      return "* Bắt buộc";
                                    }
                                    endTimeValid = true;
                                    return null;
                                  },
                                ),
                              ),
                            )),
                        Expanded(
                            flex: 1,
                            child: IconButton(
                              icon: Icon(Icons.close),
                              onPressed: () {
                                endTime = null;
                                endTimeController.text = "";
                              },
                            )),
                      ],
                    ),
                    SizedBox(
                      height: 20,
                    ),
                    Container(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                          Text(
                            "Thứ trong tuần: ",
                            style: titleStyle,
                          ),
                          (weekdays[0] ||
                                  weekdays[1] ||
                                  weekdays[2] ||
                                  weekdays[3] ||
                                  weekdays[4] ||
                                  weekdays[5] ||
                                  weekdays[6])
                              ? Container()
                              : Text(
                                  " * Chọn ít nhất 1 ngày trong tuần",
                                  style: TextStyle(color: Colors.red),
                                )
                        ])),
                    SizedBox(height: 10,),
                    WeekdaySelector(
                      onChanged: (int day) {
                        debugPrint(day.toString());
                        setState(() {
                          weekdays[day%7] = !weekdays[day%7];
                          debugPrint(day.toString());
                        });
                      },
                      shortWeekdays: ["CN", "2", "3", "4", "5", "6", "7"],
                      values: weekdays,
                    ),
                    SizedBox(
                      height: 10,
                    ),
                    Row(
                      children: [
                        Expanded(
                            flex: 2,
                            child: Text(
                              "Ngày bắt đầu:",
                              style: titleStyle,
                            )),
                        Expanded(
                            flex: 4,
                            child: InkWell(
                              onTap: () {
                                DatePicker.showDatePicker(context,
                                    maxTime: endDate!=null?endDate!:null,
                                    locale: LocaleType.vi,
                                    onConfirm: (DateTime value) {
                                  startDate = value;
                                  startDateController.text =
                                      DateFormat("dd-MM-yyyy").format(value);
                                  setState(() {});
                                });
                              },
                              child: IgnorePointer(
                                child: TextFormField(
                                  textAlign: TextAlign.center,
                                  controller: startDateController,
                                  autovalidateMode: AutovalidateMode.always,
                                  decoration: InputDecoration(
                                      errorStyle: TextStyle(color: Colors.red)),
                                  validator: (time) {
                                    if (time == null || time == "") {
                                      startDateValid = false;
                                      return "* Bắt buộc";
                                    }
                                    startDateValid = true;
                                    return null;
                                  },
                                ),
                              ),
                            )),
                        Expanded(
                            flex: 1,
                            child: IconButton(
                              icon: Icon(Icons.close),
                              onPressed: () {
                                startDate = null;
                                startDateController.text = "";
                              },
                            )),
                      ],
                    ),
                    SizedBox(
                      height: 10,
                    ),
                    Row(
                      children: [
                        Expanded(
                            flex: 2,
                            child: Text(
                              "Ngày kết thúc:",
                              style: titleStyle,
                            )),
                        Expanded(
                            flex: 4,
                            child: InkWell(
                              onTap: () {
                                DatePicker.showDatePicker(context,
                                    minTime: startDate!=null?startDate!:null,
                                    locale: LocaleType.vi,
                                    onConfirm: (DateTime value) {
                                  endDate = value;
                                  endDateController.text =
                                      DateFormat("dd-MM-yyyy").format(value);
                                  setState(() {});
                                });
                              },
                              child: IgnorePointer(
                                child: TextFormField(
                                  textAlign: TextAlign.center,
                                  controller: endDateController,
                                ),
                              ),
                            )),
                        Expanded(
                            flex: 1,
                            child: IconButton(
                              icon: Icon(Icons.close),
                              onPressed: () {
                                endDate = null;
                                endDateController.text = "";
                              },
                            )),
                      ],
                    ),
                    SizedBox(
                      height: 30,
                    ),
                    Center(
                      child: Container(
                        height: 40,
                        width: 120,
                        child: RaisedButton(
                          color: Colors.blue,
                          onPressed: () async {
                            if (!checkValidBeforeSubmit()) {
                              return;
                            }
                            showDialog(context: context,builder: (context){return AlertDialog(content: Container(height: 50,child: Center(child: CircularProgressIndicator()),),);});
                            MsgInfoCode? returnCode = await BkrmService()
                                .createShift(
                                    name: nameController.value.text,
                                    startTime: startTime,
                                    endTime: endTime,
                                    monday: weekdays[1],
                                    tuesday: weekdays[2],
                                    wednesday: weekdays[3],
                                    thursday: weekdays[4],
                                    friday: weekdays[5],
                                    saturday: weekdays[6],
                                    sunday: weekdays[0],
                                    startDate: startDate,
                                    endDate: endDate);
                            Navigator.pop(context);
                            if (returnCode == MsgInfoCode.actionSuccess) {
                              showDialog(
                                  context: context,
                                  builder: (context) {
                                    return AlertDialog(
                                      title: Text("Tạo ca làm việc thành công."),
                                      actions: [
                                        FlatButton(
                                            onPressed: () {
                                              clearPage();
                                              needRefresh=true;
                                              Navigator.pop(context);
                                              Navigator.pop(context,needRefresh);
                                            },
                                            child: Text("Hoàn thành"))
                                      ],
                                    );
                                  });
                            } else {
                              showDialog(
                                  context: context,
                                  builder: (context) {
                                    return AlertDialog(
                                      title: Text("Tạo ca làm việc thất bại"),
                                      actions: [
                                        FlatButton(
                                            onPressed: () {
                                              Navigator.pop(context);
                                            },
                                            child: Text("Đóng"))
                                      ],
                                    );
                                  });
                            }
                          },
                          child: Text(
                            "Tạo",
                            style: titleStyle,
                          ),
                        ),
                      ),
                    )
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}
