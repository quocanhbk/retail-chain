import 'package:bkrm/services/info/hrInfo/employeeInfo.dart';
import 'package:bkrm/services/info/hrInfo/shiftInfo.dart';
import 'package:bkrm/services/services.dart';
import 'package:flutter/material.dart';
import 'package:flutter_datetime_picker/flutter_datetime_picker.dart';
import 'package:intl/intl.dart';

class AddNewSchedulePage extends StatefulWidget {
  ShiftInfo shift;

  AddNewSchedulePage(this.shift);

  @override
  _AddNewSchedulePageState createState() => _AddNewSchedulePageState();
}

class _AddNewSchedulePageState extends State<AddNewSchedulePage> {
  bool needRefresh = false;

  TextEditingController employeeController = TextEditingController();
  TextEditingController startDateController = TextEditingController();
  TextEditingController endDateController = TextEditingController();

  DateTime? startDate;
  DateTime? endDate;

  bool employeeValid = false;
  bool startDateValid = false;

  late EmployeeInfo employee;

  TextStyle titleStyle = TextStyle(fontSize: 16, fontWeight: FontWeight.bold);
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(
          "Thêm lịch trình",
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
                            showDialog(
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
                                });
                          },
                          child: IgnorePointer(
                            child: TextFormField(
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
                            DatePicker.showDatePicker(context,
                                maxTime: endDate!=null?endDate:null,
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
                            DatePicker.showDatePicker(context,
                                minTime: startDate!=null?startDate:null,
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
                        ))
                  ],
                ),
                SizedBox(
                  height: 40,
                ),
                Container(
                  height: 50,
                  width: 120,
                  child: RaisedButton(
                    color: Colors.blue,
                    child: Text(
                      "Thêm",
                      style:
                          TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                    ),
                    onPressed: () async {
                      showDialog(
                          context: context,
                          builder: (context) {
                            return AlertDialog(
                              content: Container(
                                height: 50,
                                child: Center(child: CircularProgressIndicator()),
                              ),
                            );
                          });
                      MsgInfoCode? returnCode = await BkrmService()
                          .createSchedule(
                              shiftId: widget.shift.shiftId,
                              userListId: [employee.userId],
                              startDate: startDate,
                              endDate: endDate);
                      Navigator.pop(context);
                      if (returnCode == MsgInfoCode.actionSuccess) {
                        showDialog(
                            context: context,
                            builder: (context) {
                              return AlertDialog(
                                title: Text("Tạo lịch trình thành công"),
                                actions: [
                                  FlatButton(
                                      onPressed: () {
                                        needRefresh = true;
                                        Navigator.pop(context, needRefresh);
                                      },
                                      child: Text("Đóng"))
                                ],
                              );
                            });
                      } else {
                        if(returnCode==MsgInfoCode.alreadyWorkInShift){
                          showDialog(
                              context: context,
                              builder: (context) {
                                return AlertDialog(
                                  title: Text("Nhân viên này đã có lịch trình trong ca làm việc này",textAlign: TextAlign.center,),
                                  actions: [
                                    FlatButton(
                                        onPressed: () {
                                          Navigator.pop(context);
                                        },
                                        child: Text("Đóng"))
                                  ],
                                );
                              });
                          return;
                        }
                        showDialog(
                            context: context,
                            builder: (context) {
                              return AlertDialog(
                                title: Text("Tạo lịch trình thất bại"),
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
                  ),
                )
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
    List<EmployeeInfo> returnResult = await BkrmService().getEmployee();
    debugPrint("Done");
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
                    maxHeight: MediaQuery.of(context).size.height*2/3
                  ),
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
                          title: Text(employees[index].name.toString(),style: TextStyle(fontSize: 16,fontWeight: FontWeight.bold),),
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
