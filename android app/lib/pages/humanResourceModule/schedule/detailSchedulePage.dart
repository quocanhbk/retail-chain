import 'package:bkrm/pages/humanResourceModule//schedule/viewAttendance.dart';
import 'package:bkrm/services/info/hrInfo/attendanceInfo.dart';
import 'package:bkrm/services/info/hrInfo/employeeInfo.dart';
import 'package:bkrm/services/info/hrInfo/scheduleInfo.dart';
import 'package:bkrm/services/info/hrInfo/shiftInfo.dart';
import 'package:bkrm/services/services.dart';
import 'package:flutter/material.dart';
import 'package:flutter_datetime_picker/flutter_datetime_picker.dart';
import 'package:intl/intl.dart';
import 'package:page_transition/page_transition.dart';

import 'package:bkrm/pages/Nav2App.dart';

class DetailSchedulePage extends StatefulWidget {
  ShiftInfo shift;
  ScheduleInfo schedule;
  AttendanceInfo? todayAttendance;
  DetailSchedulePage(this.shift, this.schedule,this.todayAttendance);


  @override
  _DetailSchedulePageState createState() => _DetailSchedulePageState();
}

class _DetailSchedulePageState extends State<DetailSchedulePage> {
  bool needRefresh = false;

  TextEditingController employeeController = TextEditingController();
  TextEditingController startDateController = TextEditingController();
  TextEditingController endDateController = TextEditingController();

  DateTime? startDate;
  DateTime? endDate;

  bool enableEdit = false;

  bool employeeValid = false;
  bool startDateValid = false;

  bool alreadyCreateAttendance=false;
  bool inSchedule = false;

  EmployeeInfo? employee;

  TextStyle titleStyle = TextStyle(fontSize: 16, fontWeight: FontWeight.bold);

  @override
  void initState() {
    super.initState();
    setUpScheduleInfo();
  }

  setUpScheduleInfo() {
    employeeController.text = widget.schedule.name!;
    startDateController.text =
        DateFormat("dd-MM-yyyy").format(widget.schedule.scheduleStartDate!);
    startDate = widget.schedule.scheduleStartDate;
    if (widget.schedule.scheduleEndDate != null) {
      endDateController.text =
          DateFormat("dd-MM-yyyy").format(widget.schedule.scheduleEndDate!);
      startDate = widget.schedule.scheduleEndDate;
    }
    switch (DateFormat('EEEE').format(DateTime.now())) {
      case "Monday":
        if (widget.shift.monday!) {
          inSchedule = true;
        }
        break;
      case "Tuesday":
        if (widget.shift.tuesday!) {
          inSchedule = true;
        }
        break;
      case "Wednesday":
        if (widget.shift.wednesday!) {
          inSchedule = true;
        }
        break;
      case "Thursday":
        if (widget.shift.thursday!) {
          inSchedule = true;
        }
        break;
      case "Friday":
        if (widget.shift.friday!) {
          inSchedule = true;
        }
        break;
      case "Saturday":
        if (widget.shift.saturday!) {
          inSchedule = true;
        }
        break;
      case "Sunday":
        if (widget.shift.sunday!) {
          inSchedule = true;
        }
        break;
      default:
        inSchedule = false;
    }
    if(widget.todayAttendance!=null){
      if(widget.todayAttendance!.date==null){
        alreadyCreateAttendance=false;
      }else{
        alreadyCreateAttendance=true;
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(
          "Lịch trình",
        ),
      ),
      body: WillPopScope(
        onWillPop: () async {
          Navigator.pop(context, needRefresh);
          return needRefresh;
        },
        child: SingleChildScrollView(
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
                          "Nhân viên:",
                          style: titleStyle,
                        )),
                    Expanded(
                        flex: 3,
                        child: InkWell(
                          onTap: () {
/*                            showDialog(
                                context: context,
                                builder: (context) {
                                  return EmployeeDialog(
                                    onTapEmployee: (employee) {
                                      setState(() {
                                        this.employee = employee;
                                        employeeController.text = employee.name;
                                      });
                                    },
                                  );
                                });*/
                          },
                          child: IgnorePointer(
                            child: TextFormField(
                              enabled: false,
                              textAlign: TextAlign.center,
                              controller: employeeController,
                              autovalidateMode: AutovalidateMode.always,
                              validator: (employee) {
                                if (employee == null || employee == "") {
                                  employeeValid = false;
                                  return " *Bắt buộc";
                                }
                                employeeValid = true;
                                return null;
                              },
                            ),
                          ),
                        ))
                  ],
                ),
                SizedBox(
                  height: 20,
                ),
                Row(
                  children: [
                    Expanded(
                        flex: 1,
                        child: Text(
                          "Ngày bắt đầu:",
                          style: titleStyle,
                        )),
                    Expanded(
                        flex: 3,
                        child: InkWell(
                          onTap: () {
                            if (!enableEdit) {
                              return;
                            }
                            DatePicker.showDatePicker(context,
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
                              validator: (date) {
                                if (date == null || date == "") {
                                  employeeValid = false;
                                  return " *Bắt buộc";
                                }
                                employeeValid = true;
                                return null;
                              },
                            ),
                          ),
                        ))
                  ],
                ),
                SizedBox(
                  height: 20,
                ),
                Row(
                  children: [
                    Expanded(
                        flex: 1,
                        child: Text(
                          "Ngày kết thúc:",
                          style: titleStyle,
                        )),
                    Expanded(
                        flex: 3,
                        child: InkWell(
                          onTap: () {
                            if (!enableEdit) {
                              return;
                            }
                            DatePicker.showDatePicker(context,
                                locale: LocaleType.vi,
                                onConfirm: (DateTime value) {
                              if (!enableEdit) {
                                return;
                              }
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
                        ))
                  ],
                ),
                SizedBox(
                  height: 40,
                ),
                Row(
                    mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                    children: [
/*                    Container(
                    height: 50,
                    width: 120,
                    child: RaisedButton(
                      color: Colors.blue,
                      child: Text(
                        enableEdit?"Xác nhận":"Chỉnh sửa",
                        style:
                        TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                      ),
                      onPressed: () async {
                        if(!enableEdit){
                          enableEdit=true;
                          setState(() {

                          });
                          return;
                        }
                      }
                    ),
                  ),*/
                      Container(
                        height: 60,
                        width: 140,
                        child: RaisedButton(
                            color: Colors.blue,
                            child: Text(
                              alreadyCreateAttendance?"Đã điểm danh":"Điểm danh hôm nay",
                              textAlign: TextAlign.center,
                              style: TextStyle(
                                  fontSize: 18, fontWeight: FontWeight.bold),
                            ),
                            onPressed: (inSchedule&&!alreadyCreateAttendance)?() async {
                              showDialog(
                                  context: context,
                                  builder: (context) {
                                    return AlertDialog(
                                      content: Container(
                                        height: 50,
                                        child: Center(
                                          child: CircularProgressIndicator(),
                                        ),
                                      ),
                                    );
                                  });
                              MsgInfoCode returnResult = (await BkrmService()
                                  .createAttendance([widget.schedule]))!;
                              Navigator.pop(context);
                              if (returnResult == MsgInfoCode.actionSuccess) {
                                this.setState(() {
                                  alreadyCreateAttendance=true;
                                });
                                showDialog(
                                    context: context,
                                    builder: (context) {
                                      return AlertDialog(
                                        title: Text("Điểm danh thành công"),
                                        actions: [
                                          TextButton(
                                              onPressed: () {
                                                Navigator.pop(context);
                                              },
                                              child: Text("Đóng"))
                                        ],
                                      );
                                    });
                              } else {
                                showDialog(
                                    context: context,
                                    builder: (context) {
                                      return AlertDialog(
                                        title: Text("Điểm danh thất bại"),
                                        actions: [
                                          TextButton(
                                              onPressed: () {
                                                Navigator.pop(context);
                                              },
                                              child: Text("Đóng"))
                                        ],
                                      );
                                    });
                              }
                            }:null),
                      ),
                      Container(
                        height: 60,
                        width: 140,
                        child: RaisedButton(
                            color: Colors.blue,
                            child: Text(
                              "Xem ngày làm việc",
                              textAlign: TextAlign.center,
                              style: TextStyle(
                                  fontSize: 18, fontWeight: FontWeight.bold),
                            ),
                            onPressed: () async {
                              Navigator.push(context,
                                  PageTransition(child: ViewAttendancePage(widget.schedule), type: pageTransitionType));
                            }),
                      )
                    ])
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class EmployeeDialog extends StatefulWidget {
  Function(EmployeeInfo)? onTapEmployee;

  EmployeeDialog({Function(EmployeeInfo)? onTapEmployee}) {
    this.onTapEmployee = onTapEmployee;
  }

  @override
  _EmployeeDialogState createState() => _EmployeeDialogState();
}

class _EmployeeDialogState extends State<EmployeeDialog> {
  Future<List<EmployeeInfo>?> getEmployees() async {
    List<EmployeeInfo>? returnResult = await BkrmService().getEmployee();
    return returnResult;
  }

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      title: Text(
        "Nhân viên",
        style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
      ),
      content: Container(
        width: MediaQuery.of(context).size.width * 2 / 3,
        child: FutureBuilder(
          future: getEmployees(),
          builder: (context, snapshot) {
            if (!snapshot.hasData) {
              return Container(
                height: 50,
                child: Center(child: CircularProgressIndicator()),
              );
            } else {
              if (snapshot.hasError) {
                return Container(
                  height: 50,
                  constraints: BoxConstraints(
                      maxHeight: MediaQuery.of(context).size.height * 2 / 3),
                  child: Center(
                    child: Text(
                      "Đã có lỗi xảy ra!",
                      style:
                          TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                    ),
                  ),
                );
              } else {
                List<EmployeeInfo> employees =
                    snapshot.data as List<EmployeeInfo>;
                if (employees.isEmpty) {
                  return Container(
                      height: 50,
                      child: Center(
                        child: Text("Không có nhân viên."),
                      ));
                }
                return ListView.builder(
                    shrinkWrap: true,
                    itemCount: employees.length,
                    itemBuilder: (context, index) {
                      return Card(
                        elevation: 4.0,
                        child: ListTile(
                          onTap: () {
                            if (widget.onTapEmployee != null) {
                              widget.onTapEmployee!(employees[index]);
                              Navigator.pop(context);
                            }
                          },
                          title: Text(
                            employees[index].name.toString(),
                            style: TextStyle(
                                fontSize: 16, fontWeight: FontWeight.bold),
                          ),
                          subtitle: employees[index].phone == null
                              ? null
                              : Text(employees[index].phone!),
                        ),
                      );
                    });
              }
            }
          },
        ),
      ),
      actions: [
        FlatButton(
          onPressed: () {
            Navigator.pop(context);
          },
          child: Text("Đóng"),
        )
      ],
    );
  }
}
