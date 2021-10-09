import 'package:bkrm/services/info/hrInfo/employeeInfo.dart';
import 'package:bkrm/services/services.dart';
import 'package:flutter/material.dart';

class ChangePasswordPage extends StatefulWidget {
  EmployeeInfo employee;

  ChangePasswordPage(this.employee);

  @override
  _ChangePasswordPageState createState() => _ChangePasswordPageState();
}

class _ChangePasswordPageState extends State<ChangePasswordPage> {
  GlobalKey<FormState> _formKey = GlobalKey();

  TextEditingController newPasswordController = TextEditingController();
  TextEditingController confirmNewPasswordController = TextEditingController();

  bool newPasswordValid = false;
  bool confirmNewPasswordValid = false;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      resizeToAvoidBottomInset: true,
      appBar: AppBar(
          title: Text(
            "Thay đổi mật khẩu",
          )),
      body: SingleChildScrollView(
        child: Container(
          padding: EdgeInsets.all(8.0),
          child: Form(
            key: _formKey,
            child: Column(
              children: [
                SizedBox(
                  height: 50,
                ),
                Row(
                  children: [
                    Expanded(flex: 1, child: Text("Mật khẩu mới :",style: TextStyle(fontSize: 18,fontWeight: FontWeight.w500),)),
                    Expanded(
                        flex: 2,
                        child: TextFormField(
                          controller: newPasswordController,
                          obscureText: true,
                          autovalidateMode: AutovalidateMode.always,
                          validator: (newPassword) {
                            if (newPassword == null || newPassword == "") {
                              newPasswordValid=false;
                              return " * Bắt buộc";
                            }
                            newPasswordValid=true;
                            return null;
                          },
                        )),
                  ],
                ),
                SizedBox(height: 10,),
                Row(
                  children: [
                    Expanded(flex: 1, child: Text("Nhập lại mật khẩu mới :",style: TextStyle(fontSize: 18,fontWeight: FontWeight.w500),)),
                    Expanded(
                        flex: 2,
                        child: TextFormField(
                          controller: confirmNewPasswordController,
                          obscureText: true,
                          autovalidateMode: AutovalidateMode.always,
                          validator: (confirmNewPassword) {
                            if (confirmNewPassword == null || confirmNewPassword == "") {
                              confirmNewPasswordValid=false;
                              return " * Bắt buộc";
                            }
                            if(confirmNewPassword!=newPasswordController.value.text){
                              confirmNewPasswordValid=false;
                              return " * Nhập lại đúng mật khẩu mới";
                            }
                            confirmNewPasswordValid=true;
                            return null;
                          },
                        )),
                  ],
                ),
                SizedBox(height: 50,),
                Center(
                  child: ElevatedButton(
                    onPressed: ()async{
                      _formKey.currentState!.validate();
                      if(confirmNewPasswordValid&&newPasswordValid){
                        showDialog(context: context, builder: (context){
                          return AlertDialog(
                            content: Container(
                              height: 50,
                              child: Center(
                                child: CircularProgressIndicator(),
                              ),
                            ),
                          );
                        });
                        MsgInfoCode returnCode = await BkrmService().resetPasswordUserForOwner(userId:widget.employee.userId, newPassword:newPasswordController.value.text);
                        Navigator.pop(context);
                        if(returnCode==MsgInfoCode.actionSuccess){
                          showDialog(context: context, builder: (context){
                            return AlertDialog(
                              title: Text("Thay đổi mật khẩu thành công."),
                              actions: [
                                TextButton(
                                    onPressed: (){
                                      Navigator.pop(context);
                                      Navigator.pop(context);
                                    }, child: Text("Hoàn thành"))
                              ],
                            );
                          });
                        }else{
                          if(returnCode==MsgInfoCode.wrongPasswordOrUsername){
                            showDialog(context: context, builder: (context){
                              return AlertDialog(
                                title: Text("Mật khẩu hiện tại không đúng."),
                                actions: [
                                  TextButton(onPressed: (){Navigator.pop(context);}, child: Text("Đóng"))
                                ],
                              );
                            });
                          }else{
                            showDialog(context: context, builder: (context){
                              return AlertDialog(
                                title: Text("Thay đổi mật khẩu thất bại."),
                                actions: [
                                  TextButton(onPressed: (){Navigator.pop(context);}, child: Text("Đóng"))
                                ],
                              );
                            });
                          }
                        }
                      }
                    },
                    child: Container(
                      color: Colors.blue,
                      padding: EdgeInsets.all(10),
                      child: Text("Xác nhận"),
                    ),
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
