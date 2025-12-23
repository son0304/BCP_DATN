export interface Post {
  id: number;
  title: string;
  content: string;
  created_at: string;
  author: {
    id: number;
    name: string;
  };
  tags: {
    id: number;
    name: string;
  }[];
  images?: { url: string }[];
}
