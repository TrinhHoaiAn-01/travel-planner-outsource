from typing import Literal
from pydantic import BaseModel, ConfigDict, Field


class Entity(BaseModel):
    model_config = ConfigDict(extra="forbid")
    id: str = Field(min_length=1)
    name: str
    type: Literal[
        "city", 
        "destination",
        "unknown"
    ]