<template>
    <div id="user">
        <h1>vue2.0+axios+elementui 简单的增删改查</h1>
        <el-row class="line-display">
            <el-col :span="22" :offset="1">
                <div class="grid-content" style="margin-top:30px;">
                    <!-- 操作 -->
                    <ul class="btn-edit fr">
                        <li >
                            <el-button type="primary" @click="dialogCreateVisible = true"> <i class="el-icon-plus iconss" ></i>添加用户</el-button>
                        </li>
                    </ul>
                    <!-- 用户列表-->
                    <el-col :span="24" style="height:20px;"></el-col>
                    <el-table :data="users"
                              stripe
                              v-loading="loading"
                              element-loading-text="拼命加载中..."
                              style="width: 100%;margin-top:20px;"
                              height="443"
                              @selection-change="tableSelectionChange" >
                        <el-table-column sortable="custom"
                                         prop="username"
                                         label="用户名"
                                         width="100">
                        </el-table-column>
                        <el-table-column prop="name"
                                         label="姓名"
                                         width="80">
                        </el-table-column>
                        <el-table-column prop="phone"
                                         label="手机">
                        </el-table-column>
                        <el-table-column prop="email"
                                         label="邮箱">
                        </el-table-column>
                        <el-table-column prop="create_time" sortable="custom"
                                         label="注册日期">

                        </el-table-column>
                        <el-table-column
                            inline-template
                            label="操作"
                            width="250">
                            <span>
                                <el-button type="danger" size="small" @click="removeUser(row)">删除</el-button>
                                <el-button type="success" size="small" @click="setCurrent(row)">编辑</el-button>
                            </span>
                        </el-table-column>
                    </el-table>
                    <el-pagination
                      background
                      layout="prev, pager, next"
                      :total="total"
                      :page-size="3"
                      @current-change="handelCurrentPage"

                    >
                    </el-pagination>
                    <div style=" width:100%; ovflow:hidden; height:40px;"></div>
                </div>
            </el-col>
        </el-row>
        <!-- 创建用户 begin-->
        <el-dialog title="创建用户" v-model="dialogCreateVisible" :close-on-click-modal="false" :close-on-press-escape="false" @close="reset" >
            <el-form id="#create" :model="create" :rules="rules" ref="create" label-width="100px">
                <el-form-item label="用户名" prop="username">
                    <el-input v-model="create.username"></el-input>
                </el-form-item>
                <el-form-item label="姓名" prop="name">
                    <el-input v-model="create.name"></el-input>
                </el-form-item>
                <el-form-item label="密码" prop="password">
                    <el-input v-model="create.password" type="password" auto-complete="off"></el-input>
                </el-form-item>
                <el-form-item label="电话" prop="phone">
                    <el-input v-model="create.phone"></el-input>
                </el-form-item>
                <el-form-item label="邮箱" prop="email">
                    <el-input v-model="create.email"></el-input>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="dialogCreateVisible = false">取 消</el-button>
                <el-button type="primary" :loading="createLoading" @click="createUser">确 定</el-button>
            </div>
        </el-dialog>
        <!-- 修改用户 begin-->
        <el-dialog title="修改用户信息" v-model="dialogUpdateVisible"  :close-on-click-modal="false" :close-on-press-escape="false">
            <el-form id="#update" :model="update" :rules="updateRules" ref="update" label-width="100px">
                <el-form-item label="姓名" prop="username">
                    <el-input v-model="update.name"></el-input>
                </el-form-item>
                <el-form-item label="电话" prop="phone">
                    <el-input v-model="update.phone"></el-input>
                </el-form-item>
                <el-form-item label="邮箱" prop="email">
                    <el-input v-model="update.email"></el-input>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="dialogUpdateVisible = false">取 消</el-button>
                <el-button type="primary" :loading="updateLoading" @click="updateUser">确 定</el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script>
    import api from '../api/api.js'
    var placeholders = {
        "name": "请输入查找姓名",
        "username": "请输入查找用户名",
        "phone": "请输入查找电话"
    };
    export default {
        name:'',
        data : function () {
            var validatePass = (rule, value, callback) => {
                if (value === '') {
                    callback(new Error('请再次输入密码'));
                } else if (value !== this.create.password) {
                    callback(new Error('两次输入密码不一致!'));
                } else {
                    callback();
                }
            };
            return{
                users:[],

                create: {
                    id: '',
                    username: '',
                    name: '',
                    password: '',
                    checkPass: '',
                    phone: '',
                    email: '',
                    is_active: true
                },
                currentId: '',
                update: {
                    name: '',
                    phone: '',
                    email: '',
                    is_active: true
                },
                rules: {
                    name: [{
                        required: true,
                        message: '请输入姓名',
                        trigger: 'blur'
                    }, {
                        min: 3,
                        max: 15,
                        message: '长度在 3 到 15 个字符'
                    }],
                    username: [{
                        required: true,
                        message: '请输入用户名',
                        trigger: 'blur'
                    }, {
                        min: 3,
                        max: 25,
                        message: '长度在 3 到 25 个字符'
                    }, {
                        pattern: /^[A-Za-z0-9]+$/,
                        message: '用户名只能为字母和数字'
                    }],
                    password: [{
                        required: true,
                        message: '请输入密码',
                        trigger: 'blur'
                    }, {
                        min: 6,
                        max: 25,
                        message: '长度在 6 到 25 个字符'
                    }],
                    checkPass: [{
                        required: true,
                        message: '请再次输入密码',
                        trigger: 'blur'
                    }, {
                        validator: validatePass
                    }],
                    email: [{
                        type: 'email',
                        message: '邮箱格式不正确'
                    }],
                    phone: [{
                        pattern: /^1[34578]\d{9}$/,
                        message: '请输入中国国内的手机号码'
                    }]
                },
                updateRules: {
                    name: [{
                        required: true,
                        message: '请输入姓名',
                        trigger: 'blur'
                    }, {
                        min: 3,
                        max: 15,
                        message: '长度在 3 到 15 个字符'
                    }],
                    email: [{
                        type: 'email',
                        message: '邮箱格式不正确'
                    }],
                    phone: [{
                        pattern: /^1[34578]\d{9}$/,
                        message: '请输入中国国内的手机号码'
                    }]
                },
                total:0,
                pageSize:0,
                loading:true,
                keywords: '', //搜索框的关键字内容
                select: 'username', //搜索框的搜索字段
                selected: [], //已选择项
                dialogCreateVisible: false, //创建对话框的显示状态
                dialogUpdateVisible: false, //编辑对话框的显示状态
                createLoading: false,
                updateLoading: false,
                placeholder: placeholders["username"]
            }
        },
        mounted:function(){
            this.getUsers();
        },
        methods:{

            tableSelectionChange(val) {
                this.selected = val;
            },

           // 重置表单
            reset() {
                this.$refs.create.resetFields();
            },
            setCurrent(user) {
                this.currentId = user.id;
                this.update.name = user.username;
                this.update.phone = user.phone;
                this.update.email = user.email;
                this.dialogUpdateVisible = true;
            },
            //获取用户数据
            getUsers(page = 1){
                this.loading = true;
                api._get({page}).then(res => {
                    this.users = res.data;
                    this.total = res.total;
                    this.loading=false;
                },err => {
                    console.log(err);
                })
            },
            //创建用户
            createUser() {
                this.$refs.create.validate((valid) => {
                    if (valid) {
                        this.createLoading = true;
                        api._post(this.create).then(res => {
                            this.$message.success('创建用户成功！');
                            this.dialogCreateVisible = false;
                            this.createLoading = false;
                            this.reset();
                            this.getUsers();
                        }).catch((res) => {
                            var data = res;
                            if (data instanceof Array) {
                              this.$message.error(data[0]["message"]);
                            } else if (data instanceof Object) {
                              this.$message.error(data["message"]);
                            }
                            this.createLoading = false;
                        });
                    } else {
                      //this.$message.error('存在输入校验错误!');
                      return false;
                    }
                });
            },
            handelCurrentPage(currentPage){
                this.getUsers(currentPage);
            },

            // 更新用户资料
            updateUser() {
                this.$refs.update.validate((valid) => {
                    if (valid) {
                        this.updateLoading = true;
                        api._update(this.currentId, this.update).then(res => {
                            this.$message.success('修改用户资料成功！');
                            this.dialogUpdateVisible = false;
                            this.updateLoading = false;
                            this.getUsers();
                        }).catch((res) => {
                            var data = res;
                            if (data instanceof Array) {
                                this.$message.error(data[0]["message"]);
                            } else if (data instanceof Object) {
                                this.$message.error(data["message"]);
                            }
                            this.updateLoading = false;
                          });
                    } else {
                        return false;
                    }
                });
            },

            // 删除单个用户
            removeUser(user) {
                this.$confirm('此操作将永久删除用户 ' + user.username + ', 是否继续?', '提示', {
                  type: 'warning'
                }).then(() => {
                    api._remove(user).then(res => {
                        this.$message.success('成功删除了用户' + user.username + '!');
                        this.getUsers();
                        console.log(user.id);
                    }).catch((res) => {
                        this.$message.error('删除失败!');
                    });
                }).catch(() => {
                    this.$message.info('已取消操作!');
                });
            },

        }
    }
</script>
<style>
ul li{list-style: none}
.tc{text-align:center; }
.mg{ margin-top:10px;}
.fl{float:left}
.fr{float:right}
h1{text-align: center;}
</style>
