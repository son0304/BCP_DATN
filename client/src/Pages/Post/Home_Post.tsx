import {
    Avatar,
    Image,
    Typography,
    Space,
    Card,
    List,
    Button,
    Input,
    Modal,
    message,
    Select,
    Upload,
    Dropdown,
    Popconfirm,
} from "antd";
import {
    UserOutlined,
    LikeOutlined,
    MessageOutlined,
    ShareAltOutlined,
    EllipsisOutlined,
    PictureOutlined,
    EditOutlined,
    DeleteOutlined,
} from "@ant-design/icons";
import { useFetchData, usePostData, usePutData, useDeleteData } from "../../Hooks/useApi";
import { useState } from "react";
import dayjs from "dayjs";

const { Text, Paragraph, Title } = Typography;

function CommentItem({
    comment,
    postId,
    refetch,
}: {
    comment: any;
    postId: number;
    refetch: () => void;
}) {
    const [showReply, setShowReply] = useState(false);
    const [reply, setReply] = useState("");
    const createComment = usePostData("comments");

    const submitReply = async () => {
        if (!reply.trim()) return;

        await createComment.mutateAsync({
            post_id: postId,
            content: reply,
            parent_id: comment.id,
        });

        setReply("");
        setShowReply(false);
        refetch();
    };

    return (
        <div className="ml-10 mt-2">
            <div className="flex gap-2">
                <Avatar size={28} icon={<UserOutlined />} />
                <div>
                    <Text strong className="text-sm">
                        {comment.author?.name}
                    </Text>
                    <div className="text-sm">{comment.content}</div>

                    <span
                        className="text-xs text-gray-500 cursor-pointer hover:underline"
                        onClick={() => setShowReply(!showReply)}
                    >
                        Trả lời
                    </span>
                </div>
            </div>

            {showReply && (
                <div className="ml-8 mt-2 flex gap-2">
                    <Input
                        size="small"
                        value={reply}
                        onChange={(e) => setReply(e.target.value)}
                        onPressEnter={submitReply}
                        placeholder="Viết trả lời..."
                    />
                    <Button size="small" onClick={submitReply}>
                        Gửi
                    </Button>
                </div>
            )}

            {comment.replies?.map((c: any) => (
                <CommentItem
                    key={c.id}
                    comment={c}
                    postId={postId}
                    refetch={refetch}
                />
            ))}
        </div>
    );
}

function CommentSection({ postId }: { postId: number }) {
    const { data, refetch } = useFetchData<any>(
        `posts/${postId}/comments`
    );
    const comments = data?.data || [];
    const [content, setContent] = useState("");
    const createComment = usePostData("comments");

    const submit = async () => {
        if (!content.trim()) return;

        await createComment.mutateAsync({
            post_id: postId,
            content,
        });

        setContent("");
        refetch();
    };

    return (
        <div className="mt-3 border-t pt-3">
            <div className="flex gap-2 mb-3 items-start">
                <div className="shrink-0">
                    <Avatar size={32} icon={<UserOutlined />} />
                </div>

                <Input
                    value={content}
                    onChange={(e) => setContent(e.target.value)}
                    onPressEnter={submit}
                    placeholder="Viết bình luận..."
                />

                <Button onClick={submit}>Gửi</Button>
            </div>

            {comments.map((c: any) => (
                <CommentItem
                    key={c.id}
                    comment={c}
                    postId={postId}
                    refetch={refetch}
                />
            ))}
        </div>
    );
}

export default function Home_Post() {
    const [currentPage, setCurrentPage] = useState(1);
    const { data: response, isLoading, refetch } = useFetchData<any>("posts", {
        page: currentPage,
    });

    const posts = response?.data?.data || [];
    const { data: tagRes } = useFetchData<any>("tags");
    const tags = tagRes?.data || [];

    const meta = response?.data;

    const [openCreatePost, setOpenCreatePost] = useState(false);
    const [openEditPost, setOpenEditPost] = useState(false);
    const [editingPost, setEditingPost] = useState<any>(null);

    const [content, setContent] = useState("");
    const [selectedTagId, setSelectedTagId] = useState<number | null>(null);
    const [selectedTags, setSelectedTags] = useState<number[]>([]);
    const [uploadedImages, setUploadedImages] = useState<any[]>([]);
    const [uploading, setUploading] = useState(false);

    const [openCommentPostId, setOpenCommentPostId] = useState<number | null>(null);

    // Hooks
    const createPostMutation = usePostData<any, any>("posts");
    const updatePostMutation = usePutData<any, any>("posts");
    const deletePostMutation = useDeleteData("posts");

    /* ================= UPLOAD IMAGE ================= */
    const handleUploadImage = async (file: File) => {
        setUploading(true);

        const formData = new FormData();
        formData.append("type", "post");
        formData.append("id", "0");
        formData.append("files[]", file);

        try {
            const res = await fetch("http://localhost:8000/api/upload", {
                method: "POST",
                headers: {
                    Authorization: `Bearer ${localStorage.getItem("token")}`,
                    Accept: "application/json",
                },
                body: formData,
            });

            const data = await res.json();

            if (data.success) {
                setUploadedImages((prev) => [...prev, ...data.images]);
                message.success("Upload ảnh thành công");
            } else {
                console.error("Upload failed:", data);
                message.error(data.message || "Upload ảnh thất bại");
            }
        } catch (error) {
            console.error("Upload error:", error);
            message.error("Lỗi upload ảnh");
        } finally {
            setUploading(false);
        }

        return false;
    };

    const removeImage = (id: number) => {
        setUploadedImages(prev => prev.filter(img => img.id !== id));
    };

    /* ================= CREATE POST ================= */
    const handleCreatePost = async () => {
        if (!content.trim()) {
            message.error("Vui lòng nhập nội dung");
            return;
        }
        if (!selectedTagId) {
            message.error("Vui lòng chọn ít nhất 1 tag");
            return;
        }

        try {
            await createPostMutation.mutateAsync({
                title: "Bài viết mới",
                content,
                image_ids: uploadedImages.map((img) => img.id),
                tag_id: selectedTagId,
                tags: selectedTags,
            });

            message.success("Đã đăng bài viết 🎉");
            setOpenCreatePost(false);
            setContent("");
            setSelectedTagId(null);
            setSelectedTags([]);
            setUploadedImages([]);
            setCurrentPage(1);
            refetch?.();
        } catch (error: any) {
            message.error(error?.response?.data?.message || "Không thể đăng bài");
        }
    };

    /* ================= EDIT POST ================= */
    const handleOpenEdit = (post: any) => {
        setEditingPost(post);
        setContent(post.content);

        // Set tag_id chính (tag đầu tiên hoặc từ post.tag_id)
        const mainTagId = post.tag_id || post.tags?.[0]?.id || null;
        setSelectedTagId(mainTagId);

        // Set các tags phụ
        setSelectedTags(post.tags?.map((tag: any) => tag.id) || []);
        setUploadedImages(post.images || []);
        setOpenEditPost(true);
    };

    const handleUpdatePost = async () => {
        if (!content.trim()) {
            message.error("Vui lòng nhập nội dung");
            return;
        }
        if (!selectedTagId) {
            message.error("Vui lòng chọn ít nhất 1 tag");
            return;
        }
        if (!editingPost) return;

        try {
            await updatePostMutation.mutateAsync({
                id: editingPost.id,
                data: {
                    title: editingPost.title || "Bài viết",
                    content,
                    image_ids: uploadedImages.map((img) => img.id),
                    tag_id: selectedTagId,
                    tags: selectedTags,
                }
            });

            message.success("Đã cập nhật bài viết 🎉");
            setOpenEditPost(false);
            setEditingPost(null);
            setContent("");
            setSelectedTagId(null);
            setSelectedTags([]);
            setUploadedImages([]);
            refetch?.();
        } catch (error: any) {
            console.error("Update error:", error);
            message.error(error?.response?.data?.message || "Không thể cập nhật bài");
        }
    };

    /* ================= DELETE POST ================= */
    const handleDeletePost = async (postId: number) => {
        try {
            await deletePostMutation.mutateAsync(postId);
            message.success("Đã xóa bài viết");
            refetch?.();
        } catch (error: any) {
            message.error(error?.response?.data?.message || "Không thể xóa bài");
        }
    };

    /* ================= DROPDOWN MENU ================= */
    const getPostMenuItems = (post: any) => [
        {
            key: 'edit',
            label: 'Chỉnh sửa',
            icon: <EditOutlined />,
            onClick: () => handleOpenEdit(post),
        },
        {
            key: 'delete',
            label: (
                <Popconfirm
                    title="Xóa bài viết"
                    description="Bạn có chắc muốn xóa bài viết này?"
                    onConfirm={() => handleDeletePost(post.id)}
                    okText="Xóa"
                    cancelText="Hủy"
                    okButtonProps={{ danger: true }}
                >
                    <span className="text-red-500">Gỡ</span>
                </Popconfirm>
            ),
            icon: <DeleteOutlined className="text-red-500" />,
        },
    ];

    const PostImages = ({ images }: { images: any[] }) => {
    const [visible, setVisible] = useState(false);
    const [current, setCurrent] = useState(0);

    if (!images || images.length === 0) return null;

    const displayImages = images.slice(0, 2);
    const remain = images.length - 2;

    return (
        <div className="mx-[-16px] mt-3 border-y border-gray-100 bg-white overflow-hidden">
            <Image.PreviewGroup
                items={images.map((img) => ({
                    src: img.url,
                }))}
                preview={{
                    visible,
                    current,
                    onVisibleChange: (v) => setVisible(v),
                    onChange: (index) => setCurrent(index),
                }}
            >
                <div
                    className="grid grid-cols-2 gap-[2px] w-full"
                    style={{ height: 400 }}
                >
                    {displayImages.map((img, index) => (
                        <div
                            key={img.id}
                            className="relative w-full h-full overflow-hidden cursor-pointer"
                            onClick={() => {
                                setCurrent(index);
                                setVisible(true);
                            }}
                        >
                            <Image
                                src={img.url}
                                preview={false} // 👈 RẤT QUAN TRỌNG
                                className="w-full h-full"
                                style={{
                                    width: "100%",
                                    height: "100%",
                                    objectFit: "cover",
                                }}
                            />

                            {/* Overlay +n */}
                            {index === 1 && remain > 0 && (
                                <div className="absolute inset-0 bg-black/50 flex items-center justify-center pointer-events-none">
                                    <span className="text-white text-3xl font-bold">
                                        +{remain}
                                    </span>
                                </div>
                            )}
                        </div>
                    ))}
                </div>
            </Image.PreviewGroup>
        </div>
    );
};




    /* ================= HANDLE TAG SELECTION ================= */
    const handleTagChange = (values: number[]) => {
        setSelectedTags(values);
        // Tag đầu tiên sẽ là tag_id chính
        if (values.length > 0) {
            setSelectedTagId(values[0]);
        } else {
            setSelectedTagId(null);
        }
    };

    return (
        <div className="bg-gray-100 min-h-screen">
            {/* ================= HEADER ================= */}
            <div className="sticky top-0 z-20 bg-white/90 backdrop-blur border-b border-blue-100">
                <div className="w-full px-6 py-4 flex justify-center">
                    <div className="flex items-center gap-3 group">
                        <img
                            src="/logo.png"
                            alt="Logo"
                            className="w-9 h-9 md:w-12 md:h-12 rounded-full object-cover shadow-sm
                            group-hover:rotate-12 transition-transform duration-300"
                        />
                        <Title
                            level={2}
                            className="!mb-0 font-bold tracking-wide
                            bg-gradient-to-r from-blue-600 to-cyan-500
                            bg-clip-text text-transparent"
                        >
                            Cộng đồng Booking Court Prime
                        </Title>
                    </div>
                </div>
            </div>

            {/* ================= CONTENT ================= */}
            <div className="flex justify-center p-4">
                <div className="w-full max-w-[600px]">
                    {/* CREATE POST CARD */}
                    <Card className="mb-[10px] shadow-sm border-none rounded-lg">
                        <div className="flex items-center gap-3">
                            <Avatar size={40} icon={<UserOutlined />} />
                            <div
                                onClick={() => setOpenCreatePost(true)}
                                className="flex-1 bg-gray-100 hover:bg-gray-200 transition rounded-full px-4 py-2 cursor-pointer"
                            >
                                <Text type="secondary">
                                    Bạn đang nghĩ gì thế?
                                </Text>
                            </div>
                        </div>
                    </Card>

                    {/* POSTS */}
                    <List
                        loading={isLoading}
                        dataSource={posts}
                        renderItem={(item: any) => (
                            <List.Item style={{ padding: 0, marginBottom: 10 }} className="mt-[10px]">
                                <Card
                                    className="shadow-sm border-none rounded-lg"
                                    bodyStyle={{ padding: "12px 16px" }}
                                    actions={[
                                        <Space key="like"><LikeOutlined /> Thích</Space>,
                                        <Space
                                            key="comment"
                                            onClick={() =>
                                                setOpenCommentPostId(
                                                    openCommentPostId === item.id ? null : item.id
                                                )
                                            }
                                        >
                                            <MessageOutlined /> Bình luận
                                        </Space>,
                                        <Space key="share"><ShareAltOutlined /> Chia sẻ</Space>,
                                    ]}
                                >
                                    <div className="flex justify-between items-start mb-3">
                                        <Space size={12} align="start">
                                            <Avatar
                                                size={40}
                                                icon={<UserOutlined />}
                                                src={item.author?.avatar}
                                            />

                                            <div>
                                                <Space size={6}>
                                                    <Text strong>
                                                        {item.author?.name || "Người dùng"}
                                                    </Text>

                                                    {item.is_active === 0 && (
                                                        <span className="text-xs px-2 py-[2px] rounded-full 
                                                            bg-yellow-100 text-yellow-700 border border-yellow-300">
                                                            Chờ duyệt
                                                        </span>
                                                    )}
                                                </Space>

                                                <div className="text-xs text-gray-500">
                                                    {dayjs(item.created_at).format("DD/MM/YYYY HH:mm")} · 🌍
                                                </div>
                                            </div>
                                        </Space>

                                        <Dropdown
                                            menu={{ items: getPostMenuItems(item) }}
                                            trigger={['click']}
                                            placement="bottomRight"
                                        >
                                            <EllipsisOutlined className="text-lg cursor-pointer text-gray-500 hover:bg-gray-100 rounded-full p-2" />
                                        </Dropdown>
                                    </div>

                                    <Paragraph
                                        ellipsis={{ rows: 3, expandable: true, symbol: "Xem thêm" }}
                                        className="text-[15px] whitespace-pre-wrap"
                                    >
                                        {item.content}
                                    </Paragraph>

                                    {item.images?.length > 0 && <PostImages images={item.images} />}


                                    {openCommentPostId === item.id && (
                                        <CommentSection postId={item.id} />
                                    )}
                                </Card>
                            </List.Item>
                        )}
                        pagination={{
                            current: meta?.current_page,
                            pageSize: meta?.per_page || 10,
                            total: meta?.total || 0,
                            hideOnSinglePage: true,
                            onChange: (page) => {
                                setCurrentPage(page);
                                window.scrollTo({ top: 0, behavior: "smooth" });
                            },
                        }}
                    />
                </div>
            </div>

            {/* ================= MODAL CREATE POST ================= */}
            <Modal
                open={openCreatePost}
                onCancel={() => {
                    setOpenCreatePost(false);
                    setContent("");
                    setSelectedTagId(null);
                    setSelectedTags([]);
                    setUploadedImages([]);
                }}
                footer={null}
                centered
                width={500}
                destroyOnClose
                title={
                    <div className="text-center font-semibold text-lg border-b pb-2">
                        Tạo bài viết
                    </div>
                }
            >
                <div className="flex items-center justify-between mb-3">
                    <div className="flex items-center gap-3">
                        <Avatar size={40} icon={<UserOutlined />} />
                        <Text strong>Thanh Tung</Text>
                    </div>

                    <Select
                        mode="multiple"
                        allowClear
                        showSearch={false}
                        size="small"
                        placeholder="Chọn tag"
                        className="min-w-[180px]"
                        value={selectedTags}
                        onChange={handleTagChange}
                        options={tags.map((tag: any) => ({
                            label: tag.name,
                            value: tag.id,
                        }))}
                    />
                </div>

                <div className="relative">
                    <Input.TextArea
                        autoFocus
                        value={content}
                        onChange={(e) => setContent(e.target.value)}
                        placeholder="Bạn đang nghĩ gì thế?"
                        bordered={false}
                        autoSize={{ minRows: 4, maxRows: 8 }}
                        className="text-lg pr-10"
                    />

                    <Upload
                        beforeUpload={handleUploadImage}
                        showUploadList={false}
                        multiple
                        disabled={uploading}
                    >
                        <PictureOutlined
                            className={`absolute bottom-2 right-2 text-2xl cursor-pointer ${uploading ? "text-gray-400" : "text-green-500"
                                }`}
                        />
                    </Upload>
                </div>

                {uploadedImages.length > 0 && (
                    <div className="mt-3 grid grid-cols-3 gap-2">
                        {uploadedImages.map((img) => (
                            <div key={img.id} className="relative group">
                                <Image
                                    src={img.url}
                                    className="rounded w-full h-28 object-cover"
                                    preview
                                />
                                <Button
                                    type="primary"
                                    danger
                                    size="small"
                                    shape="circle"
                                    className="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition"
                                    onClick={() => removeImage(img.id)}
                                >
                                    ✕
                                </Button>
                            </div>
                        ))}
                    </div>
                )}

                <Button
                    type="primary"
                    block
                    size="large"
                    className="mt-4"
                    loading={createPostMutation.isPending}
                    disabled={!content.trim() || !selectedTagId}
                    onClick={handleCreatePost}
                >
                    Đăng
                </Button>
            </Modal>

            {/* ================= MODAL EDIT POST ================= */}
            <Modal
                open={openEditPost}
                onCancel={() => {
                    setOpenEditPost(false);
                    setEditingPost(null);
                    setContent("");
                    setSelectedTagId(null);
                    setSelectedTags([]);
                    setUploadedImages([]);
                }}
                footer={null}
                centered
                width={500}
                destroyOnClose
                title={
                    <div className="text-center font-semibold text-lg border-b pb-2">
                        Chỉnh sửa bài viết
                    </div>
                }
            >
                <div className="flex items-center justify-between mb-3">
                    <div className="flex items-center gap-3">
                        <Avatar size={40} icon={<UserOutlined />} />
                        <Text strong>{editingPost?.author?.name || "Thanh Tung"}</Text>
                    </div>

                    <Select
                        mode="multiple"
                        allowClear
                        showSearch={false}
                        size="small"
                        placeholder="Chọn tag"
                        className="min-w-[180px]"
                        value={selectedTags}
                        onChange={handleTagChange}
                        options={tags.map((tag: any) => ({
                            label: tag.name,
                            value: tag.id,
                        }))}
                    />
                </div>

                <div className="relative">
                    <Input.TextArea
                        autoFocus
                        value={content}
                        onChange={(e) => setContent(e.target.value)}
                        placeholder="Bạn đang nghĩ gì thế?"
                        bordered={false}
                        autoSize={{ minRows: 4, maxRows: 8 }}
                        className="text-lg pr-10"
                    />

                    <Upload
                        beforeUpload={handleUploadImage}
                        showUploadList={false}
                        multiple
                        disabled={uploading}
                    >
                        <PictureOutlined
                            className={`absolute bottom-2 right-2 text-2xl cursor-pointer ${uploading ? "text-gray-400" : "text-green-500"
                                }`}
                        />
                    </Upload>
                </div>

                {uploadedImages.length > 0 && (
                    <div className="mt-3 grid grid-cols-3 gap-2">
                        {uploadedImages.map((img) => (
                            <div key={img.id} className="relative group">
                                <Image
                                    src={img.url}
                                    className="rounded w-full h-28 object-cover"
                                    preview
                                />
                                <Button
                                    type="primary"
                                    danger
                                    size="small"
                                    shape="circle"
                                    className="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition"
                                    onClick={() => removeImage(img.id)}
                                >
                                    ✕
                                </Button>
                            </div>
                        ))}
                    </div>
                )}

                <Button
                    type="primary"
                    block
                    size="large"
                    className="mt-4"
                    loading={updatePostMutation.isPending}
                    disabled={!content.trim() || !selectedTagId}
                    onClick={handleUpdatePost}
                >
                    Cập nhật
                </Button>
            </Modal>
        </div>
    );
}